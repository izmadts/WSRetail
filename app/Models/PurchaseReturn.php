<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AccountingTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReturn extends Model
{
    use SoftDeletes, AccountingTrait;

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'return_date',
        'return_no',
        'reason',
        'sub_total',
        'discount',
        'tax',
        'total_amount',
        // NOTE: was missing before, so the controller's validated
        // 'refund_method' was being silently dropped on every insert.
        // Make sure your purchase_returns migration has this column.
        'refund_method',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'return_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_no)) {
                $return->return_no = 'PR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });

        // NOTE: intentionally NOT calling updateStockAndAccounts() from a
        // `created` event here. That event fires immediately after the
        // PurchaseReturn row itself is inserted - BEFORE its items are
        // created by the controller - so $this->items would be empty and
        // stock movements would silently never get created (the same
        // ordering trap the original Purchase model had). Instead,
        // PurchaseReturnController calls updateStockAndAccounts() itself,
        // once items are guaranteed to exist.

        // Deleting is safe as a model event since items already exist
        // by the time a return is deleted.
        // NOTE: this event did not exist before - deleting a purchase
        // return silently left stock and journal entries wrong forever.
        static::deleting(function ($return) {
            $return->reverseStockAndAccounts();
        });
    }

    /**
     * Decrease stock for returned items and post the offsetting accounting
     * entry. IDEMPOTENT: no-op if stock movements already exist for this
     * return, so it's safe even if called more than once.
     */
    public function updateStockAndAccounts()
    {
        $alreadyApplied = StockMovement::where('reference_type', 'purchase_return')
            ->where('reference_id', $this->id)
            ->exists();

        if ($alreadyApplied) {
            Log::info('Stock/accounting already applied for purchase return, skipping', [
                'return_id' => $this->id,
            ]);
            return;
        }

        DB::transaction(function () {
            foreach ($this->items as $item) {
                // Locked for the duration of the transaction so a concurrent
                // sale/purchase of the same product can't interleave with
                // this read-modify-write.
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if (!$product) {
                    continue;
                }

                if ($product->current_stock < $item->quantity) {
                    throw new \Exception("Cannot return: would take {$product->name}'s stock negative. Available: {$product->current_stock}, Returning: {$item->quantity}.");
                }

                $stockBefore = $product->current_stock;
                $product->current_stock -= $item->quantity;
                $product->save();

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'reference_type' => 'purchase_return',
                    'reference_id' => $this->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->current_stock,
                    'notes' => "Purchase Return #{$this->return_no}"
                ]);
            }

            $this->postReturnAccounting();
            $this->applyToPurchase();
        });
    }

    /**
     * Reduce the linked purchase's outstanding balance by this return's
     * amount - without this, a purchase that's been partially or fully
     * returned kept showing its pre-return due_amount forever, including on
     * the "Add Payment" form.
     */
    private function applyToPurchase()
    {
        $purchase = $this->purchase;
        if (!$purchase) {
            return;
        }

        $purchase->refunded_amount = (float) $purchase->refunded_amount + (float) $this->total_amount;
        $purchase->due_amount = $purchase->total_amount - $purchase->paid_amount - $purchase->refunded_amount;
        $purchase->saveQuietly();
    }

    /**
     * Symmetric undo for applyToPurchase(), run when a return is deleted.
     */
    private function removeFromPurchase()
    {
        $purchase = $this->purchase;
        if (!$purchase) {
            return;
        }

        $purchase->refunded_amount = max(0, (float) $purchase->refunded_amount - (float) $this->total_amount);
        $purchase->due_amount = $purchase->total_amount - $purchase->paid_amount - $purchase->refunded_amount;
        $purchase->saveQuietly();
    }

    /**
     * Restore stock and remove the journal entries created for this return.
     * IDEMPOTENT: no-op if no stock movements exist.
     */
    public function reverseStockAndAccounts()
    {
        $hasMovements = StockMovement::where('reference_type', 'purchase_return')
            ->where('reference_id', $this->id)
            ->exists();

        if (!$hasMovements) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->current_stock += $item->quantity;
                    $product->save();
                }
            }

            StockMovement::where('reference_type', 'purchase_return')
                ->where('reference_id', $this->id)
                ->delete();

            JournalEntry::where('reference_type', 'purchase_return')
                ->where('reference_id', $this->id)
                ->delete();

            $this->removeFromPurchase();
        });
    }

    /**
     * Debit the offset account (Payable if the refund reduces what we owe
     * the supplier, Cash if the supplier refunded us directly) and credit
     * Inventory. Both directions use the same debit/credit pattern since a
     * Payable decrease and a Cash increase are both debits in our books.
     */
    public function postReturnAccounting()
    {
        $inventoryAccount = Account::where('code', '1030')->first();
        $payableAccount = Account::where('code', '2010')->first();
        $cashAccount = Account::where('code', '1010')->first();
        $bankAccount = Account::where('code', '1020')->first();

        // 'cash' and 'bank_transfer'/'cheque' used to both route to Cash -
        // same 2-way split Expense/Income already use (Expense.php:103), so
        // a bank-transfer refund actually leaves the Bank account balance.
        $offsetAccount = $this->refund_method === 'cash'
            ? $cashAccount
            : ($this->refund_method === 'credit' ? $payableAccount : $bankAccount);
        $isPayableReduction = $this->refund_method === 'credit';

        // Thrown (not logged-and-skipped) because stock has already been
        // mutated by the time this executes - silently returning would
        // leave inventory moved with zero ledger trail. Wrapped in
        // updateStockAndAccounts()'s DB::transaction, so throwing here
        // correctly rolls the stock change back too.
        if (!$inventoryAccount || !$offsetAccount) {
            throw new \Exception("Cannot post purchase return accounting: required chart-of-accounts entries not found for return #{$this->id} (refund method: {$this->refund_method}).");
        }

        $entries = [
            [
                'account_id' => $offsetAccount->id,
                'type' => 'debit',
                'amount' => $this->total_amount,
                'description' => "Purchase Return #{$this->return_no}" . ($isPayableReduction ? ' - Payable reduced' : ' - ' . ucfirst(str_replace('_', ' ', $this->refund_method)) . ' refund'),
            ],
            [
                'account_id' => $inventoryAccount->id,
                'type' => 'credit',
                'amount' => $this->total_amount,
                'description' => "Inventory reduction for Purchase Return #{$this->return_no}",
            ]
        ];

        $this->postDoubleEntry(
            $entries,
            'purchase_return',
            $this->id,
            $this->return_date
        );
    }

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

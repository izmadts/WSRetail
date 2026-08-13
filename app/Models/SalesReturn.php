<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Models\User;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\SalesReturnItem;
use App\Models\Product;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;

class SalesReturn extends Model
{
    use SoftDeletes, AccountingTrait;

    protected $fillable = [
        'return_no',
        'sale_id',
        'customer_id',
        'return_date',
        'reason',
        'sub_total',
        'discount',
        'tax',
        'total_amount',
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
                $return->return_no = 'SR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });

        // NOTE: stock/accounting reversal is intentionally NOT triggered
        // here. It used to run in this created() hook, but that fires
        // immediately after the header row inserts - before the controller
        // has created any SalesReturnItem rows - so $this->items was always
        // empty and the whole reversal was a silent no-op. The controller
        // now calls reverseStockAndAccounting() explicitly once items exist.

        // Before deleting, restore stock
        static::deleting(function ($return) {
            $return->restoreStockAndAccounting();
        });
    }

    /**
     * Reverse stock and accounting for sales return
     */
    public function reverseStockAndAccounting()
    {
        DB::transaction(function () {
            // Idempotent, matching Sale/Purchase/PurchaseReturn - without
            // this, calling it twice for the same return would double-
            // restock and create duplicate StockMovement rows while the
            // (already idempotent) journal-entry side only posted once.
            $alreadyApplied = StockMovement::where('reference_type', 'sales_return')
                ->where('reference_id', $this->id)
                ->exists();

            if ($alreadyApplied) {
                return;
            }

            // 1. Increase product stock (returned items)
            $this->updateProductStock();

            // 2. Create stock movement
            $this->createStockMovements();

            // 3. Reverse accounting entries
            $this->reverseAccounting();

            // 4. Reduce what's owed on the original sale by the returned amount
            $this->applyToSale();
        });
    }

    /**
     * Restore stock when return is deleted
     */
    public function restoreStockAndAccounting()
    {
        DB::transaction(function () {
            $hasMovements = StockMovement::where('reference_type', 'sales_return')
                ->where('reference_id', $this->id)
                ->exists();

            if (!$hasMovements) {
                return;
            }

            // 1. Decrease product stock (reverse the return)
            foreach ($this->items as $item) {
                // Locked for the duration of the transaction so a concurrent
                // sale/purchase of the same product can't interleave with
                // this read-modify-write.
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product->current_stock < $item->quantity) {
                    throw new \Exception("Cannot delete this sales return: reversing it would take {$product->name}'s stock negative (some of it may have been sold or moved since).");
                }
                $product->current_stock -= $item->quantity;
                $product->save();
            }

            // 2. Delete stock movements
            StockMovement::where('reference_type', 'sales_return')
                ->where('reference_id', $this->id)
                ->delete();

            // 3. Delete journal entries
            JournalEntry::where('reference_type', 'sales_return')
                ->where('reference_id', $this->id)
                ->delete();

            // 4. Put back what this return had taken off the original sale's
            // owed amount.
            $this->removeFromSale();
        });
    }

    /**
     * Reduce the linked sale's outstanding balance by this return's amount,
     * the same way SaleService::recordPayment() reduces it for a payment -
     * without this, a fully-refunded sale keeps showing its pre-return
     * total/paid/due/status forever.
     */
    private function applyToSale()
    {
        $sale = $this->sale;
        if (!$sale) {
            return;
        }

        $sale->refunded_amount = (float) $sale->refunded_amount + (float) $this->total_amount;
        // A return against a sale that's ALREADY fully paid (refund_method
        // cash/bank/cheque - real money going back out, not reducing a
        // receivable) has nothing left "owed" to floor below - without
        // max(0, ...) this went negative (e.g. -960), which read as if the
        // customer somehow owed less than nothing instead of the sale
        // simply staying settled. Confirmed live: a return on an
        // already-paid cash sale produced due_amount = -960.
        $sale->due_amount = max(0, $sale->total_amount - $sale->paid_amount - $sale->refunded_amount);
        $sale->status = $sale->due_amount <= 0 ? 'paid' : ($sale->paid_amount > 0 ? 'partial' : $sale->status);
        $sale->saveQuietly();
    }

    /**
     * Symmetric undo for applyToSale(), run when a return is deleted.
     */
    private function removeFromSale()
    {
        $sale = $this->sale;
        if (!$sale) {
            return;
        }

        $sale->refunded_amount = max(0, (float) $sale->refunded_amount - (float) $this->total_amount);
        $sale->due_amount = max(0, $sale->total_amount - $sale->paid_amount - $sale->refunded_amount);
        $sale->status = $sale->due_amount <= 0 ? 'paid' : ($sale->paid_amount > 0 ? 'partial' : 'confirmed');
        $sale->saveQuietly();
    }

    /**
     * Update product stock (increase for return)
     */
    private function updateProductStock()
    {
        foreach ($this->items as $item) {
            // Locked for the duration of the transaction, and re-cached onto
            // the item so createStockMovements() (which reads $item->product
            // right after) sees this same updated, locked instance instead
            // of a stale pre-increment one.
            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
            $product->current_stock += $item->quantity;
            $product->save();
            $item->setRelation('product', $product);
        }
    }

    /**
     * Create stock movement records
     */
    private function createStockMovements()
    {
        foreach ($this->items as $item) {
            $product = $item->product;
            $oldStock = $product->current_stock - $item->quantity;

            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'reference_type' => 'sales_return',
                'reference_id' => $this->id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'stock_before' => $oldStock,
                'stock_after' => $product->current_stock,
                'notes' => "Sales Return #{$this->return_no} - Customer: {$this->customer->name}"
            ]);
        }
    }

    /**
     * Post journal entries that reverse the effect of the original sale
     * (credit cash/receivable back, debit revenue, reverse COGS/inventory).
     * Idempotent - skips if entries already exist for this return. Public
     * (not private) so AccountReconciliationService can call it directly
     * to repost missing entries after confirming none exist.
     */
    public function reverseAccounting()
    {
        if (JournalEntry::where('reference_type', 'sales_return')->where('reference_id', $this->id)->exists()) {
            return;
        }

        $inventoryAccount = Account::where('code', '1030')->first();
        $revenueAccount = Account::where('code', '4010')->first();
        $receivableAccount = Account::where('code', '1040')->first();
        $cashAccount = Account::where('code', '1010')->first();
        $bankAccount = Account::where('code', '1020')->first();

        if (!$inventoryAccount || !$revenueAccount) {
            throw new \Exception("Cannot post sales return accounting: required chart-of-accounts entries (1030/4010) not found for return #{$this->id}.");
        }

        $entries = [];

        // Reverse Sale: Credit Cash/Bank/Receivable, Debit Revenue. Driven
        // by refund_method (how this specific return is being settled), not
        // the original sale's payment_term - a cash sale can still be
        // refunded as store credit, and vice versa. 'cash' and
        // 'bank_transfer'/'cheque' used to both route to Cash - same 2-way
        // split Expense/Income already use (Expense.php:103), so a
        // bank-transfer refund actually leaves the Bank account balance.
        $refundAccount = $this->refund_method === 'cash'
            ? $cashAccount
            : ($this->refund_method === 'credit' ? $receivableAccount : $bankAccount);
        if ($refundAccount) {
            $entries[] = [
                'account_id' => $refundAccount->id,
                'type' => 'credit',
                'amount' => $this->total_amount,
                'description' => $this->refund_method === 'credit'
                    ? "Sales Return #{$this->return_no} - Customer Credit"
                    : "Sales Return #{$this->return_no} - " . ucfirst(str_replace('_', ' ', $this->refund_method)) . " Refund",
            ];
        }

        // Debit: Sales Revenue (reverse the revenue)
        $entries[] = [
            'account_id' => $revenueAccount->id,
            'type' => 'debit',
            'amount' => $this->total_amount,
            'description' => "Sales Return #{$this->return_no} - Revenue Reversal",
        ];

        // Reverse COGS at the cost actually posted for the original sale
        // (SaleItem.unit_cost), not the product's current cost - the two
        // drift apart the moment a purchase price changes in between.
        $cogsAmount = $this->items->sum(function ($item) {
            $unitCost = $item->saleItem->unit_cost ?? null;
            if ($unitCost === null) {
                $unitCost = $item->product->purchase_price ?? 0;
            }
            return $item->quantity * $unitCost;
        });

        if ($cogsAmount > 0) {
            $cogsAccount = Account::where('code', '5010')->first();
            if ($cogsAccount) {
                $entries[] = [
                    'account_id' => $cogsAccount->id,
                    'type' => 'credit',
                    'amount' => $cogsAmount,
                    'description' => "Sales Return #{$this->return_no} - COGS Reversal",
                ];

                $entries[] = [
                    'account_id' => $inventoryAccount->id,
                    'type' => 'debit',
                    'amount' => $cogsAmount,
                    'description' => "Sales Return #{$this->return_no} - Stock Restored",
                ];
            }
        }

        $this->postDoubleEntry($entries, 'sales_return', $this->id, $this->return_date);
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

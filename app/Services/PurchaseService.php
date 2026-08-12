<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for everything that happens to stock and
 * accounting as a result of a purchase: creating it, editing it,
 * deleting it, and paying it off.
 *
 * All "apply"/"reverse" methods are idempotent - they check whether the
 * relevant StockMovement rows already exist before acting, so it is
 * always safe to call them, even more than once for the same purchase.
 * Nothing else in the app (models, controllers) should create
 * StockMovement or JournalEntry rows for a purchase directly - always
 * go through this service.
 */
class PurchaseService
{
    use AccountingTrait;


    /**
     * Apply stock + inventory/payable-or-cash accounting for a purchase.
     * IDEMPOTENT: no-op if stock movements already exist for this purchase.
     */
    public function applyStockAndAccounting(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            if (!in_array($purchase->status, ['received', 'paid', 'partial'])) {
                return;
            }

            $alreadyApplied = StockMovement::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->exists();

            if ($alreadyApplied) {
                Log::info('Stock/accounting already applied for purchase, skipping', [
                    'purchase_id' => $purchase->id,
                ]);
                return;
            }

            Log::info('Applying stock and accounting for purchase', [
                'purchase_id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
            ]);

            $purchase->load('items', 'supplier');

            $this->updateStock($purchase);
            $this->createStockMovements($purchase);
            $this->postAccounting($purchase);
        });
    }

    /**
     * Reverse stock + inventory/payable-or-cash accounting for a purchase.
     * IDEMPOTENT: no-op if no stock movements exist.
     * Does NOT touch payments - use reversePaymentsAndAccounting() for that.
     */
    public function reverseStockAndAccounting(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $hasMovements = StockMovement::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->exists();

            if (!$hasMovements) {
                Log::info('No stock movements to reverse for purchase, skipping', [
                    'purchase_id' => $purchase->id,
                ]);
                return;
            }

            Log::info('Reversing stock and accounting for purchase', [
                'purchase_id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
            ]);

            $purchase->load('items');

            $this->reverseStock($purchase);
            $this->deleteStockMovements($purchase);
            $this->deleteJournalEntries($purchase, 'purchase');
        });
    }

    /**
     * Delete all payments + payment journal entries for a purchase.
     * Only call this when the whole purchase record is being deleted -
     * NOT when just editing items (syncItemsAndUpdate leaves payments alone).
     */
    public function reversePaymentsAndAccounting(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            if ($purchase->payments()->exists()) {
                $purchase->payments()->delete();
                Log::info('Payments deleted for purchase', ['purchase_id' => $purchase->id]);
            }

            $this->deleteJournalEntries($purchase, 'purchase_payment');
        });
    }

    /**
     * Record a payment against a purchase: creates the payment row, updates
     * paid_amount/due_amount/status, and (optionally) posts the
     * payable-to-cash journal entry.
     *
     * Set $postAccounting = false for purchases where cash was already
     * credited directly at creation time (pure cash purchases) so the cash
     * outflow isn't posted twice.
     */
    public function recordPayment(Purchase $purchase, $amount, $method = 'cash', $date = null, $referenceNo = null, $notes = null, $postAccounting = true)
    {
        DB::transaction(function () use ($purchase, $amount, $method, $date, $referenceNo, $notes, $postAccounting) {
            $purchase->payments()->create([
                'supplier_id' => $purchase->supplier_id,
                'payment_date' => $date ?? now(),
                'amount' => $amount,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'notes' => $notes ?? "Payment for Purchase #{$purchase->invoice_no}",
            ]);

            $purchase->paid_amount = $purchase->paid_amount + $amount;
            $purchase->due_amount = $purchase->total_amount - $purchase->paid_amount;
            $purchase->status = $purchase->due_amount <= 0 ? 'paid' : 'partial';
            $purchase->save();

            if ($postAccounting) {
                $this->postPaymentAccounting($purchase, $amount, $date, $method);
            }

            Log::info('Payment recorded for purchase', [
                'purchase_id' => $purchase->id,
                'amount' => $amount,
                'paid_amount' => $purchase->paid_amount,
                'due_amount' => $purchase->due_amount,
                'status' => $purchase->status,
            ]);
        });
    }

    /**
     * Reverse items + re-apply stock/accounting when a purchase is edited.
     * Payments already made are left untouched. If the edit marks the
     * purchase "paid" and there's still a due amount, it is auto-settled.
     */
    public function syncItemsAndUpdate(Purchase $purchase, array $newItemsData)
    {
        DB::transaction(function () use ($purchase, $newItemsData) {
            Log::info('Syncing items and updating purchase', [
                'purchase_id' => $purchase->id,
                'invoice_no' => $purchase->invoice_no,
            ]);

            $this->reverseStockAndAccounting($purchase);

            $purchase->items()->delete();
            foreach ($newItemsData as $itemData) {
                $purchase->items()->create($itemData);
            }

            $purchase->refresh();
            $purchase->calculateTotals();
            $purchase->save();

            if (in_array($purchase->status, ['received', 'paid', 'partial'])) {
                $this->applyStockAndAccounting($purchase);
            }

            $purchase->refresh();
            if ($purchase->status == 'paid' && $purchase->due_amount > 0) {
                $this->recordPayment(
                    $purchase,
                    $purchase->due_amount,
                    $purchase->payment_term == 'cash' ? 'cash' : 'bank_transfer',
                    now(),
                    null,
                    'Full settlement on purchase update',
                    $purchase->payment_term != 'cash'
                );
            }
        });
    }

    // =============================================
    // INTERNAL HELPERS
    // =============================================

    private function updateStock(Purchase $purchase)
    {
        foreach ($purchase->items as $item) {
            // Locked for the duration of the enclosing transaction so a
            // concurrent sale/return of the same product can't interleave
            // with this read-modify-write and lose an update.
            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
            if ($product) {
                $product->current_stock += $item->quantity;
                $product->save();
                Log::info("Stock increased for product: {$product->name} (+{$item->quantity})");
            }
        }
    }

    private function reverseStock(Purchase $purchase)
    {
        foreach ($purchase->items as $item) {
            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
            if ($product) {
                if ($product->current_stock < $item->quantity) {
                    throw new \Exception("Cannot reverse purchase: would take {$product->name}'s stock negative (some of it may have already been sold or moved).");
                }
                $product->current_stock -= $item->quantity;
                $product->save();
                Log::info("Stock reversed for product: {$product->name} (-{$item->quantity})");
            }
        }
    }

    private function createStockMovements(Purchase $purchase)
    {
        foreach ($purchase->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $stockBefore = $product->current_stock - $item->quantity;

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->current_stock,
                    'notes' => "Purchase #{$purchase->invoice_no}" . ($purchase->supplier ? " - Supplier: {$purchase->supplier->name}" : ''),
                ]);
            }
        }
    }

    private function deleteStockMovements(Purchase $purchase)
    {
        StockMovement::where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)
            ->delete();
    }

    private function postAccounting(Purchase $purchase)
    {
        $inventoryAccount = Account::where('code', '1030')->first();
        $payableAccount = Account::where('code', '2010')->first();
        $cashAccount = Account::where('code', '1010')->first();

        // Thrown (not logged-and-skipped) because updateStock() has already
        // run by the time this executes - silently returning would leave
        // inventory moved with zero ledger trail. The whole call is wrapped
        // in applyStockAndAccounting()'s DB::transaction, so throwing here
        // correctly rolls the stock change back too.
        if (!$inventoryAccount || !$payableAccount || !$cashAccount) {
            throw new \Exception("Cannot post purchase accounting: required chart-of-accounts entries (1030/2010/1010) not found for purchase #{$purchase->id}.");
        }

        $entries = [
            [
                'account_id' => $inventoryAccount->id,
                'type' => 'debit',
                'amount' => $purchase->total_amount,
                'description' => "Purchase #{$purchase->invoice_no} - Stock In",
            ],
        ];

        if ($purchase->payment_term == 'credit') {
            $entries[] = [
                'account_id' => $payableAccount->id,
                'type' => 'credit',
                'amount' => $purchase->total_amount,
                'description' => "Credit Purchase #{$purchase->invoice_no}" . ($purchase->supplier ? " - Supplier: {$purchase->supplier->name}" : ''),
            ];
        } else {
            $entries[] = [
                'account_id' => $cashAccount->id,
                'type' => 'credit',
                'amount' => $purchase->total_amount,
                'description' => "Cash Purchase #{$purchase->invoice_no}",
            ];
        }

        // Routed through postDoubleEntry() (AccountingTrait) instead of a
        // raw create() loop - it's the one place in the app that actually
        // asserts a batch's debits equal its credits, and dated by the
        // purchase's own date instead of always "today" so ledger-dated
        // reports (Trial Balance) agree with purchase-date-filtered ones
        // for the same transaction.
        $this->postDoubleEntry($entries, 'purchase', $purchase->id, $purchase->purchase_date);

        Log::info("Accounting entries posted for purchase: {$purchase->invoice_no}");
    }

    private function postPaymentAccounting(Purchase $purchase, $amount, $date = null, $method = 'cash')
    {
        // Cash-term purchases already credited Cash directly at creation
        // (see postAccounting() above) - posting again here for a payment
        // against one would debit Cash a second time for money that only
        // left the business once. Guarded here, not just at the call site,
        // so it holds regardless of which caller forgets to pass
        // $postAccounting = false.
        if ($purchase->payment_term !== 'credit') {
            return;
        }

        // Guards against posting a payment (Dr Payable / Cr Cash) for a
        // purchase whose own Dr Inventory / Cr Payable entry was never
        // posted (e.g. still 'draft'/'ordered') - that would debit down a
        // payable balance that was never actually credited up.
        $hasBaseEntry = JournalEntry::where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)
            ->exists();
        if (!$hasBaseEntry) {
            throw new \Exception("Cannot record payment: Purchase #{$purchase->invoice_no} has not been received/posted yet.");
        }

        $payableAccount = Account::where('code', '2010')->first();
        // Routes to Cash(1010) or Bank(1020) based on the payment method the
        // form actually collected - same 2-way split Expense/Income already
        // use (Expense.php:103). Previously this always credited 1010
        // regardless of $method, so a payment explicitly recorded as "Bank
        // Transfer" or "Cheque" silently posted to Cash instead.
        $creditAccount = $method === 'cash'
            ? Account::where('code', '1010')->first()
            : Account::where('code', '1020')->first();

        if (!$payableAccount || !$creditAccount) {
            throw new \Exception("Cannot post purchase payment accounting: required chart-of-accounts entries (2010/1010/1020) not found for purchase #{$purchase->id}.");
        }

        $this->postDoubleEntry([
            [
                'account_id' => $payableAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Payment for Purchase #{$purchase->invoice_no}",
            ],
            [
                'account_id' => $creditAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Cash Payment for Purchase #{$purchase->invoice_no}",
            ],
        ], 'purchase_payment', $purchase->id, $date ? \Carbon\Carbon::parse($date) : null);
    }

    private function deleteJournalEntries(Purchase $purchase, $referenceType = 'purchase')
    {
        JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $purchase->id)
            ->delete();
    }

    /**
     * Repost ONLY the 'purchase' journal entries, without touching stock.
     *
     * For the AccountReconciliationService: applyStockAndAccounting()'s own
     * idempotency guard checks StockMovement, not JournalEntry, so it can't
     * repair a purchase whose StockMovement rows exist but whose journal
     * entries were somehow lost - it would just see "already applied" and
     * no-op. This method guards on JournalEntry directly instead. Caller is
     * responsible for confirming the purchase's status still warrants
     * posting (see applyStockAndAccounting()'s own status check).
     */
    public function repostAccountingOnly(Purchase $purchase): bool
    {
        if (JournalEntry::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->exists()) {
            return false;
        }

        $purchase->load('items', 'supplier');

        DB::transaction(function () use ($purchase) {
            $this->postAccounting($purchase);
        });

        return true;
    }

    /**
     * Repost ALL 'purchase_payment' journal entries for a purchase from its
     * real PurchasePayment rows - deletes whatever's there for this
     * purchase and replays every payment through postPaymentAccounting().
     * Fixes missing, duplicate, and wrong-amount payment entries in one
     * call, the same "delete everything, replay from source" idea
     * Supplier/Customer::updatePayment() already uses for direct payments.
     */
    public function repostAllPaymentAccounting(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $this->deleteJournalEntries($purchase, 'purchase_payment');

            if ($purchase->payment_term !== 'credit') {
                return;
            }

            foreach ($purchase->payments()->orderBy('payment_date')->get() as $payment) {
                $this->postPaymentAccounting($purchase, $payment->amount, $payment->payment_date, $payment->payment_method);
            }
        });
    }
}

<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for everything that happens to stock and
 * accounting as a result of a sale: creating it, editing it, deleting it,
 * and recording payments against it. Mirrors PurchaseService's shape.
 *
 * All "apply"/"reverse" methods are idempotent - they check whether the
 * relevant StockMovement rows already exist before acting, so it is always
 * safe to call them more than once for the same sale. Nothing else in the
 * app (models, controllers) should create StockMovement or JournalEntry
 * rows for a sale directly - always go through this service.
 */
class SaleService
{
    use AccountingTrait;

    /**
     * Apply stock + inventory/revenue accounting for a sale.
     * IDEMPOTENT: no-op if stock movements already exist for this sale.
     */
    public function applyStockAndAccounting(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            // 'partial' included alongside 'confirmed'/'paid' - a sale with
            // a real partial payment is a completely normal state, and
            // omitting it here meant editing such a sale (syncItemsAndUpdate
            // calls this to re-apply after reversing) silently skipped
            // re-posting its stock/accounting, permanently losing them.
            if (!in_array($sale->status, ['confirmed', 'paid', 'partial'])) {
                return;
            }

            $alreadyApplied = StockMovement::where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->exists();

            if ($alreadyApplied) {
                Log::info('Stock/accounting already applied for sale, skipping', ['sale_id' => $sale->id]);
                return;
            }

            $sale->load('items', 'customer');

            $this->updateStock($sale);
            $this->createStockMovements($sale);
            $this->postAccounting($sale);
        });
    }

    /**
     * Reverse stock + inventory/revenue accounting for a sale. IDEMPOTENT:
     * no-op if no stock movements exist.
     *
     * Used by both the edit flow (syncItemsAndUpdate, which re-applies fresh
     * afterward) and full deletion (reverseForDeletion, below). Deliberately
     * does NOT touch recorded payments - editing a sale must leave its
     * payment history alone; deletion handles payments itself.
     */
    public function reverseStockAndAccounting(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $hasMovements = StockMovement::where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->exists();

            if (!$hasMovements) {
                Log::info('No stock movements to reverse for sale, skipping', ['sale_id' => $sale->id]);
                return;
            }

            $sale->load('items');

            $this->reverseStock($sale);
            $this->deleteStockMovements($sale);
            $this->deleteJournalEntries($sale, 'sale');
        });
    }

    /**
     * Full teardown for a sale that's being deleted outright: reverses
     * stock/accounting (via reverseStockAndAccounting above), then also
     * removes any payments recorded against it and their journal
     * entries - without this, deleting a partially-paid sale leaves its
     * Cash/Receivable payment entries in the ledger forever with no sale
     * behind them. Not used by edits; those keep payment history intact.
     */
    public function reverseForDeletion(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $this->reverseStockAndAccounting($sale);
            $this->deleteJournalEntries($sale, 'sale_payment');
            $sale->payments()->delete();
        });
    }

    /**
     * Reverse items + re-apply stock/accounting when a sale is edited.
     * Payments already recorded are left untouched. Sub-total is always
     * recomputed from the new item set (not trusted from the caller) so
     * revenue/COGS get re-posted for what the sale actually contains now,
     * not whatever total was on record before the edit.
     */
    public function syncItemsAndUpdate(Sale $sale, array $newItemsData)
    {
        DB::transaction(function () use ($sale, $newItemsData) {
            $this->reverseStockAndAccounting($sale);

            $sale->items()->delete();
            foreach ($newItemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            $sale->refresh();
            $sale->sub_total = $sale->items()->sum('total_price');
            $sale->calculateTotals();

            // A sale that already carries real payments can't be silently
            // downgraded to draft/confirmed by whatever the edit form's
            // status field happened to submit - it must keep reflecting
            // what's actually been paid, same rule recordPayment() applies.
            if ((float) $sale->paid_amount > 0) {
                $sale->status = $sale->due_amount <= 0 ? 'paid' : 'partial';
            }

            $sale->save();

            if (in_array($sale->status, ['confirmed', 'paid', 'partial'])) {
                $this->applyStockAndAccounting($sale);
            }
        });
    }

    /**
     * Record a payment against a sale: creates the payment row, updates
     * paid_amount/due_amount/recovery_percentage/status, and posts the
     * cash-from-receivable journal entry for credit sales.
     */
    public function recordPayment(Sale $sale, $amount, $method = 'cash', $date = null, $referenceNo = null, $notes = null)
    {
        DB::transaction(function () use ($sale, $amount, $method, $date, $referenceNo, $notes) {
            $sale->payments()->create([
                'customer_id' => $sale->customer_id,
                'payment_date' => $date ?? now(),
                'amount' => $amount,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'notes' => $notes ?? "Payment for Sale #{$sale->invoice_no}",
            ]);

            $sale->paid_amount = $sale->paid_amount + $amount;
            $sale->due_amount = $sale->total_amount - $sale->paid_amount;
            $sale->status = $sale->due_amount <= 0 ? 'paid' : 'partial';
            $sale->save();

            $this->postPaymentAccounting($sale, $amount, $method);

            Log::info('Payment recorded for sale', [
                'sale_id' => $sale->id,
                'amount' => $amount,
                'paid_amount' => $sale->paid_amount,
                'due_amount' => $sale->due_amount,
                'status' => $sale->status,
            ]);
        });
    }

    // =============================================
    // INTERNAL HELPERS
    // =============================================

    /**
     * A variant line item's stock lives on the ProductVariant row, not the
     * parent Product - resolves and locks whichever one actually holds the
     * quantity for this item.
     */
    private function stockableFor($item)
    {
        return $item->product_variant_id
            ? \App\Models\ProductVariant::where('id', $item->product_variant_id)->lockForUpdate()->first()
            : Product::where('id', $item->product_id)->lockForUpdate()->first();
    }

    private function updateStock(Sale $sale)
    {
        foreach ($sale->items as $item) {
            // Locked for the duration of the enclosing transaction so two
            // sales of the same product submitted at nearly the same moment
            // can't both read the same starting stock and both pass the
            // sufficiency check below (a lost-update race that could
            // oversell into negative stock).
            $stockable = $this->stockableFor($item);
            if ($stockable) {
                if ($stockable->current_stock < $item->quantity) {
                    $label = $item->product_variant_id ? $stockable->label : $stockable->name;
                    throw new \Exception("Insufficient stock for product: {$label}. Available: {$stockable->current_stock}, Required: {$item->quantity}");
                }
                $stockable->current_stock = floatval($stockable->current_stock) - floatval($item->quantity);
                $stockable->save();
            }
        }
    }

    private function reverseStock(Sale $sale)
    {
        foreach ($sale->items as $item) {
            $stockable = $this->stockableFor($item);
            if ($stockable) {
                $stockable->current_stock = floatval($stockable->current_stock) + floatval($item->quantity);
                $stockable->save();
            }
        }
    }

    private function createStockMovements(Sale $sale)
    {
        foreach ($sale->items as $item) {
            $stockable = $item->product_variant_id
                ? \App\Models\ProductVariant::find($item->product_variant_id)
                : Product::find($item->product_id);
            if ($stockable) {
                $oldStock = floatval($stockable->current_stock) + floatval($item->quantity);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'type' => 'out',
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'stock_before' => $oldStock,
                    'stock_after' => $stockable->current_stock,
                    'notes' => "Sale #{$sale->invoice_no}" . ($sale->customer ? " - Customer: {$sale->customer->name}" : ''),
                ]);
            }
        }
    }

    private function deleteStockMovements(Sale $sale)
    {
        StockMovement::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->delete();
    }

    private function postAccounting(Sale $sale)
    {
        $inventoryAccount = Account::where('code', '1030')->first();
        $revenueAccount = Account::where('code', '4010')->first();
        $receivableAccount = Account::where('code', '1040')->first();
        $cashAccount = Account::where('code', '1010')->first();

        // Thrown (not logged-and-skipped) because updateStock() has already
        // run by the time this executes - silently returning would leave
        // inventory moved with zero ledger trail. The whole call is wrapped
        // in applyStockAndAccounting()'s DB::transaction, so throwing here
        // correctly rolls the stock change back too.
        if (!$inventoryAccount || !$revenueAccount) {
            throw new \Exception("Cannot post sale accounting: required chart-of-accounts entries (1030/4010) not found for sale #{$sale->id}.");
        }

        $debitAccount = $sale->payment_term == 'cash' ? $cashAccount : $receivableAccount;
        if (!$debitAccount) {
            throw new \Exception("Cannot post sale accounting: required chart-of-accounts entry (" . ($sale->payment_term == 'cash' ? '1010' : '1040') . ") not found for sale #{$sale->id}.");
        }

        $entries = [
            [
                'account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $sale->total_amount,
                'description' => $sale->payment_term == 'cash'
                    ? "Cash Sale #{$sale->invoice_no}"
                    : "Credit Sale #{$sale->invoice_no}" . ($sale->customer ? " - Customer: {$sale->customer->name}" : ''),
            ],
            [
                'account_id' => $revenueAccount->id,
                'type' => 'credit',
                'amount' => $sale->total_amount,
                'description' => "Sale Revenue #{$sale->invoice_no}",
            ],
        ];

        // COGS: debit expense, credit inventory for the cost of goods sold.
        // Snapshot each item's unit cost the first time it's applied so a
        // later sales return can reverse COGS at the price actually posted
        // here, not whatever the product's cost has drifted to by then.
        $cogsAmount = 0;
        foreach ($sale->items as $item) {
            $unitCost = $item->unit_cost;
            if ($unitCost === null) {
                $costSource = $item->product_variant_id
                    ? \App\Models\ProductVariant::find($item->product_variant_id)
                    : Product::find($item->product_id);
                $unitCost = floatval($costSource->purchase_price ?? 0);
                $item->unit_cost = $unitCost;
                $item->saveQuietly();
            }
            $cogsAmount += floatval($item->quantity) * floatval($unitCost);
        }

        if ($cogsAmount > 0) {
            $cogsAccount = Account::where('code', '5010')->first();
            if ($cogsAccount) {
                $entries[] = [
                    'account_id' => $cogsAccount->id,
                    'type' => 'debit',
                    'amount' => $cogsAmount,
                    'description' => "COGS for Sale #{$sale->invoice_no}",
                ];

                $entries[] = [
                    'account_id' => $inventoryAccount->id,
                    'type' => 'credit',
                    'amount' => $cogsAmount,
                    'description' => "Inventory reduction for Sale #{$sale->invoice_no}",
                ];
            }
        }

        $this->postDoubleEntry($entries, 'sale', $sale->id, $sale->sale_date);

        Log::info("Accounting entries posted for sale: {$sale->invoice_no}");
    }

    private function postPaymentAccounting(Sale $sale, $amount, $method = 'cash', $date = null)
    {
        // Cash sales already debited Cash directly at creation - a payment
        // against a cash sale would double count it, so only credit sales
        // (which debited Receivable instead) post here.
        if ($sale->payment_term !== 'credit') {
            return;
        }

        // Guards against posting a payment (Dr Cash / Cr Receivable) for a
        // sale whose own Dr Receivable / Cr Revenue entry was never posted
        // (e.g. still 'draft') - that would credit down a receivable
        // balance that was never actually debited up.
        $hasBaseEntry = JournalEntry::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->exists();
        if (!$hasBaseEntry) {
            throw new \Exception("Cannot record payment: Sale #{$sale->invoice_no} has not been confirmed/posted yet.");
        }

        // Routes to Cash(1010) or Bank(1020) based on the payment method the
        // form actually collected - same 2-way split Expense/Income already
        // use (Expense.php:103). Previously this always hit 1010 regardless
        // of $method, so a payment explicitly recorded as "Bank Transfer" or
        // "Cheque" via Add Payment silently posted to Cash instead - the
        // Cash account absorbed money that never touched it while Bank
        // never moved, which is exactly what a real bank reconciliation
        // would catch as wrong. Confirmed live: a bank-transfer payment
        // left the Bank account balance untouched before this fix.
        $debitAccount = $method === 'cash'
            ? Account::where('code', '1010')->first()
            : Account::where('code', '1020')->first();
        $receivableAccount = Account::where('code', '1040')->first();

        if (!$debitAccount || !$receivableAccount) {
            throw new \Exception("Cannot post sale payment accounting: required chart-of-accounts entries (1010/1020/1040) not found for sale #{$sale->id}.");
        }

        $this->postDoubleEntry([
            [
                'account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Payment received for Sale #{$sale->invoice_no}",
            ],
            [
                'account_id' => $receivableAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Payment received for Sale #{$sale->invoice_no}",
            ],
        ], 'sale_payment', $sale->id, $date ? \Carbon\Carbon::parse($date) : null);
    }

    private function deleteJournalEntries(Sale $sale, $referenceType = 'sale')
    {
        JournalEntry::where('reference_type', $referenceType)
            ->where('reference_id', $sale->id)
            ->delete();
    }

    /**
     * Repost ONLY the 'sale' journal entries, without touching stock. See
     * PurchaseService::repostAccountingOnly() for why this exists -
     * applyStockAndAccounting()'s idempotency guard checks
     * StockMovement, not JournalEntry, so it can't repair a sale whose
     * StockMovement rows exist but whose journal entries were lost. Caller
     * is responsible for confirming the sale's status still warrants
     * posting (see applyStockAndAccounting()'s own status check).
     */
    public function repostAccountingOnly(Sale $sale): bool
    {
        if (JournalEntry::where('reference_type', 'sale')->where('reference_id', $sale->id)->exists()) {
            return false;
        }

        $sale->load('items', 'customer');

        DB::transaction(function () use ($sale) {
            $this->postAccounting($sale);
        });

        return true;
    }

    /**
     * Repost ALL 'sale_payment' journal entries for a sale from its real
     * SalePayment rows - same "delete everything, replay from source" idea
     * as PurchaseService::repostAllPaymentAccounting().
     */
    public function repostAllPaymentAccounting(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $this->deleteJournalEntries($sale, 'sale_payment');

            if ($sale->payment_term !== 'credit') {
                return;
            }

            foreach ($sale->payments()->orderBy('payment_date')->get() as $payment) {
                $this->postPaymentAccounting($sale, $payment->amount, $payment->payment_method, $payment->payment_date);
            }
        });
    }
}

<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AgentCommissionLog;
use App\Models\AgentCommissionPayment;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Income;
use App\Models\JournalEntry;
use App\Models\MoneyTransfer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\CustomerPayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Scans the whole double-entry ledger for problems (missing, duplicate,
 * orphaned, unbalanced, or mismatched journal entries) across every
 * transaction type in the app, and can repair what's safely repairable.
 *
 * Every journal entry anywhere in this app is created through exactly one
 * chokepoint - AccountingTrait::postDoubleEntry() - so "repost" always
 * means "call the owning model's/service's own posting method again,"
 * never hand-rolling entries here. See the approved plan
 * (twinkly-wondering-snail.md) for the full design rationale.
 *
 * scan() is pure-read. fix() re-validates every issue live (never trusts a
 * stale scan snapshot) before touching anything, wraps each fix in its own
 * DB transaction so one bad fix can't corrupt others or abort the batch,
 * and writes a full before/after audit row to the existing ActivityLog
 * table for every change made.
 */
class AccountReconciliationService
{
    /** Reference types whose single clean post is always exactly 2 rows. */
    private const TWO_ROW_TYPES = [
        'expense', 'income', 'money_transfer', 'supplier_opening',
        'customer_opening', 'supplier_payment', 'customer_payment',
        'purchase_return', 'sales_return', 'adjustment', 'opening', 'sale_commission',
    ];

    private PurchaseService $purchaseService;
    private SaleService $saleService;
    private CommissionService $commissionService;

    public function __construct(PurchaseService $purchaseService, SaleService $saleService, CommissionService $commissionService)
    {
        $this->purchaseService = $purchaseService;
        $this->saleService = $saleService;
        $this->commissionService = $commissionService;
    }

    // =========================================================
    // SCAN
    // =========================================================

    public function scan(): array
    {
        $issues = array_merge(
            $this->scanMissing(),
            $this->scanDuplicates(),
            $this->scanOrphaned(),
            $this->scanUnbalanced(),
            $this->scanBrokenAccountRefs(),
            $this->scanAmountMismatches(),
            $this->scanPaymentAggregates()
        );

        $bySeverity = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($issues as $issue) {
            $bySeverity[$issue['severity']] = ($bySeverity[$issue['severity']] ?? 0) + 1;
        }

        return [
            'issues' => $issues,
            'summary' => [
                'total' => count($issues),
                'fixable' => count(array_filter($issues, fn ($i) => $i['fixable'])),
                'by_severity' => $bySeverity,
            ],
        ];
    }

    private function scanMissing(): array
    {
        $issues = [];

        foreach (Expense::whereIn('status', ['approved', 'paid'])->get() as $expense) {
            if (!$this->hasEntries('expense', $expense->id)) {
                $issues[] = $this->issue('missing', 'expense', $expense->id, "Expense #{$expense->expense_no} ({$expense->status}) has no ledger entries.", 'high', true, 'safe');
            }
        }

        foreach (Income::all() as $income) {
            if (!$this->hasEntries('income', $income->id)) {
                $issues[] = $this->issue('missing', 'income', $income->id, "Income #{$income->income_no} has no ledger entries.", 'high', true, 'safe');
            }
        }

        foreach (MoneyTransfer::where('status', 'completed')->get() as $transfer) {
            if (!$this->hasEntries('money_transfer', $transfer->id)) {
                $issues[] = $this->issue('missing', 'money_transfer', $transfer->id, "Money Transfer #{$transfer->transfer_no} (completed) has no ledger entries.", 'high', true, 'safe');
            }
        }

        foreach (Supplier::where('opening_balance', '>', 0)->get() as $supplier) {
            if (!$this->hasEntries('supplier_opening', $supplier->id)) {
                $issues[] = $this->issue('missing', 'supplier_opening', $supplier->id, "Supplier #{$supplier->id} ({$supplier->name}) has an opening balance but no ledger entry.", 'high', true, 'safe');
            }
        }

        foreach (Customer::where('opening_balance', '>', 0)->get() as $customer) {
            if (!$this->hasEntries('customer_opening', $customer->id)) {
                $issues[] = $this->issue('missing', 'customer_opening', $customer->id, "Customer #{$customer->id} ({$customer->name}) has an opening balance but no ledger entry.", 'high', true, 'safe');
            }
        }

        foreach (SupplierPayment::all() as $payment) {
            if (!$this->hasEntries('supplier_payment', $payment->id)) {
                $issues[] = $this->issue('missing', 'supplier_payment', $payment->id, "Direct payment #{$payment->id} to " . optional($payment->supplier)->name . " has no ledger entries.", 'high', true, 'safe');
            }
        }

        foreach (CustomerPayment::all() as $payment) {
            if (!$this->hasEntries('customer_payment', $payment->id)) {
                $issues[] = $this->issue('missing', 'customer_payment', $payment->id, "Direct payment #{$payment->id} from " . optional($payment->customer)->name . " has no ledger entries.", 'high', true, 'safe');
            }
        }

        foreach (Purchase::whereIn('status', ['received', 'paid', 'partial'])->get() as $purchase) {
            if (!$this->hasEntries('purchase', $purchase->id)) {
                $hasStock = StockMovement::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->exists();
                $issues[] = $this->issue('missing', 'purchase', $purchase->id, "Purchase #{$purchase->invoice_no} ({$purchase->status}) has no ledger entries." . ($hasStock ? ' (stock already moved - ledger-only gap)' : ''), 'critical', true, 'safe');
            }
        }

        foreach (Sale::whereIn('status', ['confirmed', 'paid', 'partial'])->get() as $sale) {
            if (!$this->hasEntries('sale', $sale->id)) {
                $hasStock = StockMovement::where('reference_type', 'sale')->where('reference_id', $sale->id)->exists();
                $issues[] = $this->issue('missing', 'sale', $sale->id, "Sale #{$sale->invoice_no} ({$sale->status}) has no ledger entries." . ($hasStock ? ' (stock already moved - ledger-only gap)' : ''), 'critical', true, 'safe');
            }
        }

        foreach (PurchaseReturn::all() as $return) {
            if (!$this->hasEntries('purchase_return', $return->id)) {
                $hasStock = StockMovement::where('reference_type', 'purchase_return')->where('reference_id', $return->id)->exists();
                $issues[] = $this->issue('missing', 'purchase_return', $return->id, "Purchase Return #{$return->return_no} has no ledger entries." . ($hasStock ? '' : ' (stock also not reversed - needs manual review)'), 'high', $hasStock, $hasStock ? 'safe' : 'manual_only');
            }
        }

        foreach (SalesReturn::all() as $return) {
            if (!$this->hasEntries('sales_return', $return->id)) {
                $hasStock = StockMovement::where('reference_type', 'sales_return')->where('reference_id', $return->id)->exists();
                $issues[] = $this->issue('missing', 'sales_return', $return->id, "Sales Return #{$return->return_no} has no ledger entries." . ($hasStock ? '' : ' (stock also not reversed - needs manual review)'), 'high', $hasStock, $hasStock ? 'safe' : 'manual_only');
            }
        }

        foreach (StockAdjustment::all() as $adjustment) {
            if ($this->hasEntries('adjustment', $adjustment->id)) {
                continue;
            }
            $product = $adjustment->product;
            $amount = $product ? round((float) $adjustment->quantity * (float) $product->purchase_price, 2) : 0;
            if ($amount <= 0) {
                continue;
            }
            $issues[] = $this->issue('missing', 'adjustment', $adjustment->id, "Stock Adjustment #{$adjustment->id} ({$product->name}) has no ledger entries. Amount would be reconstructed from the product's CURRENT purchase price (Rs. " . number_format($amount, 2) . ") - verify against historical records if the price has changed since.", 'medium', true, 'confirm_required');
        }

        foreach (Product::where('current_stock', '>', 0)->get() as $product) {
            $amount = round((float) $product->current_stock * (float) $product->purchase_price, 2);
            if ($amount <= 0 || $this->hasEntries('opening', $product->id)) {
                continue;
            }
            $hasStock = StockMovement::where('reference_type', 'opening')->where('reference_id', $product->id)->exists();
            $issues[] = $this->issue('missing', 'opening', $product->id, "Product #{$product->id} ({$product->name}) opening stock has no ledger entry.", 'medium', true, 'safe');
        }

        foreach (AgentCommissionLog::where('amount', '!=', 0)->get() as $log) {
            if (!$this->hasEntries('sale_commission', $log->id)) {
                $issues[] = $this->issue('missing', 'sale_commission', $log->id, "Commission log #{$log->id} ({$log->description}) has no ledger entry.", 'medium', true, 'safe');
            }
        }

        return $issues;
    }

    private function scanDuplicates(): array
    {
        $issues = [];

        $counts = JournalEntry::select('reference_type', 'reference_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('reference_type', array_merge(self::TWO_ROW_TYPES, ['purchase', 'sale']))
            ->groupBy('reference_type', 'reference_id')
            ->havingRaw('COUNT(*) > ?', [2])
            ->get();

        foreach ($counts as $row) {
            $isBaseTxn = in_array($row->reference_type, ['purchase', 'sale', 'sales_return']);
            // For purchase/sale/sales_return, exactly 4 rows is a
            // legitimate single post when COGS applies (revenue pair +
            // COGS pair - sales_return reverses COGS the same way a sale
            // posts it, confirmed by reading SalesReturn::reverseAccounting()
            // directly) - only >4 is an unambiguous duplicate. For every
            // other type a clean post is
            // always exactly 2 rows, so >2 is already unambiguous. This
            // deliberately under-detects a duplicated *non-COGS* sale/
            // purchase (would also show count=4) rather than risk
            // "fixing" a legitimate COGS post - a false negative is far
            // safer than a false positive here.
            if ($isBaseTxn && $row->cnt <= 4) {
                continue;
            }

            $label = $this->entityLabel($row->reference_type, $row->reference_id);
            $issues[] = $this->issue('duplicate', $row->reference_type, $row->reference_id, "{$label} has {$row->cnt} ledger entries where a clean post would have " . ($isBaseTxn ? '2 or 4' : '2') . ".", 'high', true, 'safe');
        }

        return $issues;
    }

    private function scanOrphaned(): array
    {
        $issues = [];

        $softDeleteTypes = [
            'purchase' => Purchase::class, 'purchase_payment' => Purchase::class, 'purchase_return' => PurchaseReturn::class,
            'sale' => Sale::class, 'sale_payment' => Sale::class, 'sales_return' => SalesReturn::class,
            'expense' => Expense::class, 'income' => Income::class, 'money_transfer' => MoneyTransfer::class,
        ];
        foreach ($softDeleteTypes as $type => $model) {
            $validIds = $model::withTrashed()->pluck('id');
            $orphanRefIds = JournalEntry::where('reference_type', $type)->whereNotIn('reference_id', $validIds)->distinct()->pluck('reference_id');
            foreach ($orphanRefIds as $refId) {
                $issues[] = $this->issue('orphaned', $type, $refId, "Ledger entries exist for {$type} #{$refId}, but no such record exists (even soft-deleted) - source is confirmed gone.", 'high', false, 'manual_only');
            }
        }

        // supplier_opening/customer_opening: no boot(deleting) hook cleans
        // these up (known gap) - Supplier/Customer are soft-deletable, so
        // also flag entries whose supplier/customer was soft-deleted, as a
        // separate lower-severity "source archived, entries still active"
        // notice rather than a hard orphan.
        $this->scanArchivedButActiveOpenings($issues, 'supplier_opening', Supplier::class);
        $this->scanArchivedButActiveOpenings($issues, 'customer_opening', Customer::class);

        $hardDeleteTypes = [
            'adjustment' => StockAdjustment::class, 'opening' => Product::class, 'sale_commission' => AgentCommissionLog::class,
            'supplier_payment' => SupplierPayment::class, 'customer_payment' => CustomerPayment::class,
        ];
        foreach ($hardDeleteTypes as $type => $model) {
            $validIds = $model::pluck('id');
            $orphanRefIds = JournalEntry::where('reference_type', $type)->whereNotIn('reference_id', $validIds)->distinct()->pluck('reference_id');
            foreach ($orphanRefIds as $refId) {
                $issues[] = $this->issue('orphaned', $type, $refId, "Ledger entries exist for {$type} #{$refId}, but no such record exists - source is permanently, unrecoverably gone (hard-deleted).", 'high', false, 'manual_only');
            }
        }

        return $issues;
    }

    private function scanArchivedButActiveOpenings(array &$issues, string $type, string $modelClass): void
    {
        $trashedIds = $modelClass::onlyTrashed()->pluck('id');
        $refIds = JournalEntry::where('reference_type', $type)->whereIn('reference_id', $trashedIds)->distinct()->pluck('reference_id');
        foreach ($refIds as $refId) {
            $issues[] = $this->issue('orphaned', $type, $refId, "The record behind {$type} #{$refId} was archived (soft-deleted), but its ledger entries are still active - decide whether historical entries should be kept or removed.", 'medium', false, 'manual_only');
        }
    }

    private function scanUnbalanced(): array
    {
        $issues = [];

        $rows = JournalEntry::select('reference_type', 'reference_id')
            ->selectRaw("SUM(CASE WHEN type='debit' THEN amount ELSE 0 END) as debit_total")
            ->selectRaw("SUM(CASE WHEN type='credit' THEN amount ELSE 0 END) as credit_total")
            ->groupBy('reference_type', 'reference_id')
            ->havingRaw("ROUND(SUM(CASE WHEN type='debit' THEN amount ELSE 0 END) - SUM(CASE WHEN type='credit' THEN amount ELSE 0 END), 2) != 0")
            ->get();

        foreach ($rows as $row) {
            $label = $this->entityLabel($row->reference_type, $row->reference_id);
            $diff = round((float) $row->debit_total - (float) $row->credit_total, 2);
            $issues[] = $this->issue('unbalanced', $row->reference_type, $row->reference_id, "{$label}'s ledger entries don't balance - debits and credits differ by Rs. " . number_format(abs($diff), 2) . ". This should be structurally impossible; investigate manually before touching these rows.", 'critical', false, 'manual_only');
        }

        return $issues;
    }

    private function scanBrokenAccountRefs(): array
    {
        $issues = [];
        $validAccountIds = Account::withTrashed()->pluck('id');
        $broken = JournalEntry::whereNotIn('account_id', $validAccountIds)->get();
        foreach ($broken as $entry) {
            $issues[] = $this->issue('broken_account', $entry->reference_type, $entry->reference_id, "Journal entry #{$entry->id} ({$entry->description}) references account_id {$entry->account_id}, which doesn't exist. Cannot be auto-corrected - the intended account can't be inferred.", 'critical', false, 'manual_only');
        }
        return $issues;
    }

    private function scanAmountMismatches(): array
    {
        $issues = [];

        $checks = [
            ['expense', Expense::class, 'amount'],
            ['income', Income::class, 'amount'],
            ['money_transfer', MoneyTransfer::class, 'amount'],
            ['sale_commission', AgentCommissionLog::class, 'amount'],
            ['supplier_payment', SupplierPayment::class, 'amount'],
            ['customer_payment', CustomerPayment::class, 'amount'],
        ];

        foreach ($checks as [$type, $modelClass, $field]) {
            foreach ($modelClass::all() as $record) {
                $entries = JournalEntry::where('reference_type', $type)->where('reference_id', $record->id)->get();
                if ($entries->count() !== 2) {
                    continue; // missing/duplicate already reported elsewhere
                }
                $debitEntry = $entries->firstWhere('type', 'debit');
                if (!$debitEntry) {
                    continue;
                }
                // abs() on both sides: AgentCommissionLog.amount can be
                // legitimately negative (a write-off/reversal log), but
                // postDoubleEntry() always stores a positive amount and
                // encodes direction via debit/credit type instead - the
                // other types here (Expense/Income/MoneyTransfer/
                // Supplier|CustomerPayment) are always positive already,
                // so abs() is a no-op for them.
                $expected = round(abs((float) $record->{$field}), 2);
                $actual = round(abs((float) $debitEntry->amount), 2);
                if (abs($expected - $actual) > 0.01) {
                    $label = $this->entityLabel($type, $record->id);
                    $issues[] = $this->issue('amount_mismatch', $type, $record->id, "{$label}: source amount is Rs. " . number_format($expected, 2) . " but the ledger shows Rs. " . number_format($actual, 2) . ".", 'high', true, 'safe');
                }
            }
        }

        // Purchase: debit-Inventory(1030) leg should equal total_amount.
        $inventoryAccount = Account::where('code', '1030')->first();
        if ($inventoryAccount) {
            foreach (Purchase::whereIn('status', ['received', 'paid', 'partial'])->get() as $purchase) {
                $entry = JournalEntry::where('reference_type', 'purchase')->where('reference_id', $purchase->id)
                    ->where('account_id', $inventoryAccount->id)->where('type', 'debit')->first();
                if (!$entry) {
                    continue; // missing case handled elsewhere
                }
                $expected = round((float) $purchase->total_amount, 2);
                $actual = round((float) $entry->amount, 2);
                if (abs($expected - $actual) > 0.01) {
                    $issues[] = $this->issue('amount_mismatch', 'purchase', $purchase->id, "Purchase #{$purchase->invoice_no}: total is Rs. " . number_format($expected, 2) . " but the ledger's inventory leg shows Rs. " . number_format($actual, 2) . ".", 'high', true, 'safe');
                }
            }
        }

        // Sale: credit-Revenue(4010) leg should equal total_amount (stable
        // regardless of whether COGS also posted).
        $revenueAccount = Account::where('code', '4010')->first();
        if ($revenueAccount) {
            foreach (Sale::whereIn('status', ['confirmed', 'paid', 'partial'])->get() as $sale) {
                $entry = JournalEntry::where('reference_type', 'sale')->where('reference_id', $sale->id)
                    ->where('account_id', $revenueAccount->id)->where('type', 'credit')->first();
                if (!$entry) {
                    continue;
                }
                $expected = round((float) $sale->total_amount, 2);
                $actual = round((float) $entry->amount, 2);
                if (abs($expected - $actual) > 0.01) {
                    $issues[] = $this->issue('amount_mismatch', 'sale', $sale->id, "Sale #{$sale->invoice_no}: total is Rs. " . number_format($expected, 2) . " but the ledger's revenue leg shows Rs. " . number_format($actual, 2) . ".", 'high', true, 'safe');
                }
            }
        }

        return $issues;
    }

    /**
     * purchase_payment/sale_payment/commission_payment share one reference_id
     * across many source rows, so they're checked as an aggregate sum
     * rather than a 1:1 match. commission_payment is flagged only (never
     * auto-fixed) - payCommission() allocates a lump payment across
     * whichever AgentCommissionLog rows happen to be due at the time, so a
     * fresh replay could legitimately allocate differently than history
     * actually did; there is no safe mechanical "correct" repost for it.
     */
    private function scanPaymentAggregates(): array
    {
        $issues = [];

        foreach (Purchase::where('payment_term', 'credit')->get() as $purchase) {
            $sourceSum = round((float) $purchase->payments()->sum('amount'), 2);
            $journalSum = round((float) JournalEntry::where('reference_type', 'purchase_payment')->where('reference_id', $purchase->id)->where('type', 'debit')->sum('amount'), 2);
            if (abs($sourceSum - $journalSum) > 0.01) {
                $issues[] = $this->issue('amount_mismatch', 'purchase_payment', $purchase->id, "Purchase #{$purchase->invoice_no}: payments total Rs. " . number_format($sourceSum, 2) . " but the ledger shows Rs. " . number_format($journalSum, 2) . " posted against it.", 'high', true, 'safe');
            }
        }

        foreach (Sale::where('payment_term', 'credit')->get() as $sale) {
            $sourceSum = round((float) $sale->payments()->sum('amount'), 2);
            $journalSum = round((float) JournalEntry::where('reference_type', 'sale_payment')->where('reference_id', $sale->id)->where('type', 'debit')->sum('amount'), 2);
            if (abs($sourceSum - $journalSum) > 0.01) {
                $issues[] = $this->issue('amount_mismatch', 'sale_payment', $sale->id, "Sale #{$sale->invoice_no}: payments total Rs. " . number_format($sourceSum, 2) . " but the ledger shows Rs. " . number_format($journalSum, 2) . " posted against it.", 'high', true, 'safe');
            }
        }

        $agentIds = AgentCommissionPayment::distinct()->pluck('agent_id');
        foreach ($agentIds as $agentId) {
            $sourceSum = round((float) AgentCommissionPayment::where('agent_id', $agentId)->sum('amount'), 2);
            $journalSum = round((float) JournalEntry::where('reference_type', 'commission_payment')->where('reference_id', $agentId)->where('type', 'debit')->sum('amount'), 2);
            if (abs($sourceSum - $journalSum) > 0.01) {
                $agent = User::find($agentId);
                $issues[] = $this->issue('amount_mismatch', 'commission_payment', $agentId, "Agent " . ($agent->name ?? "#{$agentId}") . ": recorded payouts total Rs. " . number_format($sourceSum, 2) . " but the ledger shows Rs. " . number_format($journalSum, 2) . ". Not auto-fixable - payouts are allocated against whichever commission logs were due at the time, which can't be safely replayed after the fact. Review manually via the Commission module.", 'medium', false, 'manual_only');
            }
        }

        return $issues;
    }

    // =========================================================
    // FIX
    // =========================================================

    public function fix(array $selectors): array
    {
        $tiers = ['opening' => [], 'base' => [], 'payment' => []];
        $openingTypes = ['supplier_opening', 'customer_opening', 'opening'];
        $baseTypes = ['purchase', 'sale', 'purchase_return', 'sales_return', 'adjustment', 'expense', 'income', 'money_transfer', 'sale_commission'];

        foreach ($selectors as $selector) {
            $type = $selector['reference_type'];
            if (in_array($type, $openingTypes)) {
                $tiers['opening'][] = $selector;
            } elseif (in_array($type, $baseTypes)) {
                $tiers['base'][] = $selector;
            } else {
                $tiers['payment'][] = $selector;
            }
        }

        $results = [];
        foreach (['opening', 'base', 'payment'] as $tier) {
            foreach ($tiers[$tier] as $selector) {
                $results[] = $this->applyOne($selector);
            }
        }
        return $results;
    }

    private function applyOne(array $selector): array
    {
        $type = $selector['reference_type'];
        $id = $selector['reference_id'];
        $category = $selector['category'];

        try {
            return DB::transaction(function () use ($type, $id, $category, $selector) {
                if (!$this->stillReproducible($type, $id, $category)) {
                    return ['selector' => $selector, 'status' => 'skipped_already_resolved'];
                }

                $before = $this->snapshotEntries($type, $id);
                $applied = $this->dispatchFix($type, $id, $category);

                if (!$applied) {
                    return ['selector' => $selector, 'status' => 'not_fixable'];
                }

                $after = $this->snapshotEntries($type, $id);
                $this->logFix($type, $id, $category, $before, $after);

                return ['selector' => $selector, 'status' => 'fixed'];
            });
        } catch (\Throwable $e) {
            Log::error('Account reconciliation fix failed', ['type' => $type, 'id' => $id, 'category' => $category, 'error' => $e->getMessage()]);
            return ['selector' => $selector, 'status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    private function stillReproducible(string $type, int $id, string $category): bool
    {
        return match ($category) {
            'missing' => !$this->hasEntries($type, $id),
            'duplicate' => JournalEntry::where('reference_type', $type)->where('reference_id', $id)->count() > 2,
            'amount_mismatch' => true, // re-derived fresh inside the fix itself
            default => false, // orphaned/unbalanced/broken_account are never auto-applied
        };
    }

    private function dispatchFix(string $type, int $id, string $category): bool
    {
        switch ($type) {
            case 'expense':
                $model = Expense::find($id);
                if (!$model || !in_array($model->status, ['approved', 'paid'])) {
                    return false;
                }
                if ($category !== 'missing') {
                    $model->reverseAccounting();
                }
                $model->postAccounting();
                return true;

            case 'income':
                $model = Income::find($id);
                if (!$model) {
                    return false;
                }
                if ($category !== 'missing') {
                    $model->reverseAccounting();
                }
                $model->postAccounting();
                return true;

            case 'money_transfer':
                $model = MoneyTransfer::find($id);
                if (!$model || $model->status !== 'completed') {
                    return false;
                }
                if ($category !== 'missing') {
                    $model->reverseAccounting();
                }
                $model->postAccounting();
                return true;

            case 'supplier_opening':
                $model = Supplier::find($id);
                if (!$model) {
                    return false;
                }
                if ($category === 'duplicate') {
                    // postOpeningBalance() only reposts when the amount
                    // differs from the *existing* entry - it won't collapse
                    // duplicates that already carry the right amount, so
                    // force a clean slate first.
                    JournalEntry::where('reference_type', 'supplier_opening')->where('reference_id', $id)->delete();
                }
                $model->postOpeningBalance();
                return true;

            case 'customer_opening':
                $model = Customer::find($id);
                if (!$model) {
                    return false;
                }
                if ($category === 'duplicate') {
                    JournalEntry::where('reference_type', 'customer_opening')->where('reference_id', $id)->delete();
                }
                $model->postOpeningBalance();
                return true;

            case 'purchase':
                $purchase = Purchase::find($id);
                if (!$purchase || !in_array($purchase->status, ['received', 'paid', 'partial'])) {
                    return false;
                }
                if ($category !== 'missing') {
                    JournalEntry::where('reference_type', 'purchase')->where('reference_id', $id)->delete();
                }
                $hasStock = StockMovement::where('reference_type', 'purchase')->where('reference_id', $id)->exists();
                if ($hasStock) {
                    return $this->purchaseService->repostAccountingOnly($purchase);
                }
                $this->purchaseService->applyStockAndAccounting($purchase);
                return true;

            case 'sale':
                $sale = Sale::find($id);
                if (!$sale || !in_array($sale->status, ['confirmed', 'paid', 'partial'])) {
                    return false;
                }
                if ($category !== 'missing') {
                    JournalEntry::where('reference_type', 'sale')->where('reference_id', $id)->delete();
                }
                $hasStock = StockMovement::where('reference_type', 'sale')->where('reference_id', $id)->exists();
                if ($hasStock) {
                    return $this->saleService->repostAccountingOnly($sale);
                }
                $this->saleService->applyStockAndAccounting($sale);
                return true;

            case 'purchase_return':
                $return = PurchaseReturn::find($id);
                if (!$return) {
                    return false;
                }
                if ($category !== 'missing') {
                    JournalEntry::where('reference_type', 'purchase_return')->where('reference_id', $id)->delete();
                }
                $return->postReturnAccounting();
                return true;

            case 'sales_return':
                $return = SalesReturn::find($id);
                if (!$return) {
                    return false;
                }
                if ($category !== 'missing') {
                    JournalEntry::where('reference_type', 'sales_return')->where('reference_id', $id)->delete();
                }
                $return->reverseAccounting();
                return true;

            case 'adjustment':
                $adjustment = StockAdjustment::find($id);
                if (!$adjustment) {
                    return false;
                }
                if ($category !== 'missing') {
                    $adjustment->reverseAccounting();
                }
                $adjustment->postAccounting();
                return true;

            case 'opening':
                $product = Product::find($id);
                if (!$product) {
                    return false;
                }
                if ($category !== 'missing') {
                    JournalEntry::where('reference_type', 'opening')->where('reference_id', $id)->delete();
                }
                $hasStock = StockMovement::where('reference_type', 'opening')->where('reference_id', $id)->exists();
                if ($hasStock) {
                    return $product->repostOpeningLedgerOnly();
                }
                $product->postOpeningStock();
                return true;

            case 'sale_commission':
                $log = AgentCommissionLog::find($id);
                if (!$log) {
                    return false;
                }
                if ($category !== 'missing') {
                    JournalEntry::where('reference_type', 'sale_commission')->where('reference_id', $id)->delete();
                }
                $this->commissionService->repostCommissionLedger($log);
                return true;

            case 'supplier_payment':
                $payment = SupplierPayment::find($id);
                if (!$payment || !$payment->supplier) {
                    return false;
                }
                // updatePayment() unconditionally deletes-then-reposts by
                // this payment's own id, fixing missing/duplicate/wrong-
                // amount in one call - same idiom used for editing a
                // payment via the UI.
                $payment->supplier->updatePayment($payment, $payment->amount, $payment->payment_method, $payment->payment_date, $payment->reference_no, $payment->notes);
                return true;

            case 'customer_payment':
                $payment = CustomerPayment::find($id);
                if (!$payment || !$payment->customer) {
                    return false;
                }
                $payment->customer->updatePayment($payment, $payment->amount, $payment->payment_method, $payment->payment_date, $payment->reference_no, $payment->notes);
                return true;

            case 'purchase_payment':
                $purchase = Purchase::find($id);
                if (!$purchase) {
                    return false;
                }
                $this->purchaseService->repostAllPaymentAccounting($purchase);
                return true;

            case 'sale_payment':
                $sale = Sale::find($id);
                if (!$sale) {
                    return false;
                }
                $this->saleService->repostAllPaymentAccounting($sale);
                return true;

            default:
                return false;
        }
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function hasEntries(string $type, int $id): bool
    {
        return JournalEntry::where('reference_type', $type)->where('reference_id', $id)->exists();
    }

    private function snapshotEntries(string $type, int $id): array
    {
        return JournalEntry::where('reference_type', $type)->where('reference_id', $id)->get()->toArray();
    }

    private function issue(string $category, string $type, int $refId, string $description, string $severity, bool $fixable, string $fixGroup): array
    {
        return [
            'id' => "{$category}:{$type}:{$refId}",
            'category' => $category,
            'reference_type' => $type,
            'reference_id' => $refId,
            'entity_label' => $this->entityLabel($type, $refId),
            'description' => $description,
            'severity' => $severity,
            'fixable' => $fixable,
            'fix_group' => $fixGroup, // 'safe' (bulk-fixable), 'confirm_required' (per-item only), 'manual_only'
        ];
    }

    private function entityLabel(string $type, $refId): string
    {
        if ($type === 'supplier_payment') {
            $payment = SupplierPayment::find($refId);
            $supplierName = optional($payment)->supplier?->name;
            return "Direct Payment #{$refId}" . ($supplierName ? " (to {$supplierName})" : '');
        }

        if ($type === 'customer_payment') {
            $payment = CustomerPayment::find($refId);
            $customerName = optional($payment)->customer?->name;
            return "Direct Payment #{$refId}" . ($customerName ? " (from {$customerName})" : '');
        }

        return match ($type) {
            'supplier_opening' => optional(Supplier::withTrashed()->find($refId))->name ? "Supplier #{$refId} (" . Supplier::withTrashed()->find($refId)->name . ')' : "Supplier #{$refId}",
            'customer_opening' => optional(Customer::withTrashed()->find($refId))->name ? "Customer #{$refId} (" . Customer::withTrashed()->find($refId)->name . ')' : "Customer #{$refId}",
            'purchase', 'purchase_payment' => optional(Purchase::withTrashed()->find($refId))->invoice_no ? 'Purchase ' . Purchase::withTrashed()->find($refId)->invoice_no : "Purchase #{$refId}",
            'sale', 'sale_payment' => optional(Sale::withTrashed()->find($refId))->invoice_no ? 'Sale ' . Sale::withTrashed()->find($refId)->invoice_no : "Sale #{$refId}",
            'purchase_return' => optional(PurchaseReturn::withTrashed()->find($refId))->return_no ? 'Purchase Return ' . PurchaseReturn::withTrashed()->find($refId)->return_no : "Purchase Return #{$refId}",
            'sales_return' => optional(SalesReturn::withTrashed()->find($refId))->return_no ? 'Sales Return ' . SalesReturn::withTrashed()->find($refId)->return_no : "Sales Return #{$refId}",
            'expense' => optional(Expense::withTrashed()->find($refId))->expense_no ? 'Expense ' . Expense::withTrashed()->find($refId)->expense_no : "Expense #{$refId}",
            'income' => optional(Income::withTrashed()->find($refId))->income_no ? 'Income ' . Income::withTrashed()->find($refId)->income_no : "Income #{$refId}",
            'money_transfer' => optional(MoneyTransfer::withTrashed()->find($refId))->transfer_no ? 'Transfer ' . MoneyTransfer::withTrashed()->find($refId)->transfer_no : "Transfer #{$refId}",
            'adjustment' => "Stock Adjustment #{$refId}",
            'opening' => optional(Product::find($refId))->name ? "Product #{$refId} (" . Product::find($refId)->name . ')' : "Product #{$refId}",
            'sale_commission' => "Commission Log #{$refId}",
            'commission_payment' => "Agent #{$refId}",
            default => "{$type} #{$refId}",
        };
    }

    private function logFix(string $type, int $id, string $category, array $before, array $after): void
    {
        $user = Auth::user();
        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'action' => 'fixed',
            'module' => 'ledger_audit',
            'description' => "Reconcile All Accounts: fixed {$category} issue for {$this->entityLabel($type, $id)} ({$type} #{$id})",
            'old_data' => $before,
            'new_data' => $after,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\User;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\AgentCommissionLog;
use App\Models\AgentCommissionPayment;
use App\Models\AgentMonthlyTarget;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for the sales-agent commission & bonus policy
 * (see the sales/marketing commission policy document). Every place that
 * used to hardcode tiers/rates/bonus amounts now reads them from here,
 * which in turn reads admin-editable Settings rows - so the business can
 * actually change its own commission policy without a code change.
 */
class CommissionService
{
    use AccountingTrait;

    private const DEFAULTS = [
        // Progressive by the agent's month-to-date cumulative cash sales.
        'commission.cash_tiers' => [
            ['from' => 0, 'to' => 300000, 'rate' => 1],
            ['from' => 300000, 'to' => 700000, 'rate' => 1.5],
            ['from' => 700000, 'to' => 1500000, 'rate' => 2],
            ['from' => 1500000, 'to' => null, 'rate' => 2.5],
        ],
        // Flat % on every credit-sale payment, recovered or not yet fully settled.
        'commission.credit_rate' => 1,
        // New customer bonus: fixed amount once a customer reaches N orders.
        'commission.new_customer_bonus_amount' => 500,
        'commission.new_customer_bonus_min_orders' => 3,
        // Recovery bonus: extra % once a sale's recovery rate crosses the threshold.
        'commission.recovery_bonus_threshold_pct' => 95,
        'commission.recovery_bonus_rate_pct' => 0.5,
        // Monthly target bonus tiers.
        'commission.target_bonus_tiers' => [
            ['achievement_pct' => 100, 'bonus' => 5000],
            ['achievement_pct' => 120, 'bonus' => 10000],
            ['achievement_pct' => 150, 'bonus' => 20000],
        ],
        // Outstanding/credit-hold policy.
        'commission.credit_hold_grace_days' => 30,
        'commission.enforce_credit_block' => false,
        // Blocks a new credit sale that would push a customer's balance
        // past their own per-customer credit_limit (0/blank = no limit set).
        'commission.enforce_credit_limit' => false,
        // Whether an agent-registered customer must be active/verified
        // before their sales earn commission (the policy's "Salesman Rule").
        'commission.require_customer_verification' => true,
    ];

    public static function getSetting($key)
    {
        $default = self::DEFAULTS[$key] ?? null;
        $raw = Setting::get($key);

        if ($raw === null) {
            return $default;
        }

        if (is_array($default)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : $default;
        }

        if (is_bool($default)) {
            return in_array($raw, ['1', 'true', 'yes'], true);
        }

        return is_numeric($raw) ? (float) $raw : $raw;
    }

    public static function setSetting($key, $value)
    {
        $stored = is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : $value);
        return Setting::set($key, $stored);
    }

    public static function defaults()
    {
        return self::DEFAULTS;
    }

    /**
     * The policy's "Salesman Rule": if this sale is agent-attributed and the
     * customer was registered by an agent but never activated, no
     * commission is earned on it at all.
     *
     * Note: there is no OTP-based "verified" status in this codebase -
     * is_active is the real signal that a customer's registration actually
     * went somewhere.
     */
    public function isCustomerCommissionEligible(Sale $sale)
    {
        if (!self::getSetting('commission.require_customer_verification')) {
            return true;
        }

        if (!$sale->agent_id) {
            return true;
        }

        $customer = $sale->customer ?? Customer::find($sale->customer_id);
        if (!$customer || !$customer->is_agent_customer) {
            return true;
        }

        return (bool) $customer->is_active;
    }

    /**
     * Cash-sale commission: brackets by the agent's month-to-date
     * cumulative CONFIRMED cash sales (including this one), rate applied
     * to this sale's own amount only. Idempotent per sale.
     */
    public function calculateCashCommission(Sale $sale)
    {
        if (!$sale->agent_id || $sale->payment_term !== 'cash' || $sale->is_commission_held) {
            return 0;
        }

        if (AgentCommissionLog::where('reference_type', 'sale')->where('reference_id', $sale->id)->exists()) {
            return 0;
        }

        if (!$this->isCustomerCommissionEligible($sale)) {
            Log::info('Commission skipped - customer not eligible', ['sale_id' => $sale->id]);
            return 0;
        }

        $monthToDate = Sale::where('agent_id', $sale->agent_id)
            ->where('payment_term', 'cash')
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->whereMonth('sale_date', $sale->sale_date->month)
            ->whereYear('sale_date', $sale->sale_date->year)
            ->sum('total_amount');

        $rate = $this->rateForCumulative((float) $monthToDate, self::getSetting('commission.cash_tiers'));
        $commissionAmount = round($sale->total_amount * $rate / 100, 2);

        if ($commissionAmount <= 0) {
            return 0;
        }

        $log = AgentCommissionLog::create([
            'agent_id' => $sale->agent_id,
            'sale_id' => $sale->id,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => $commissionAmount,
            'commission_rate' => $rate,
            'commission_type' => 'cash',
            'description' => "Cash sale commission for #{$sale->invoice_no} ({$rate}% bracket)",
            'is_paid' => false,
        ]);

        $this->postCommissionLedger($commissionAmount, $log->id, "Cash sale commission accrued - Sale #{$sale->invoice_no}", $sale->sale_date);

        $sale->commission_amount = $commissionAmount;
        $sale->commission_due_amount = $commissionAmount;
        $sale->saveQuietly();

        return $commissionAmount;
    }

    /**
     * Credit-sale commission: a flat % of EVERY payment recorded against a
     * credit sale, logged immediately - not held until the sale is 100%
     * settled. Called once per payment from SaleService::recordPayment().
     */
    public function accrueCreditCommission(Sale $sale, $paymentAmount)
    {
        if (!$sale->agent_id || $sale->payment_term !== 'credit' || $paymentAmount <= 0 || $sale->is_commission_held) {
            return 0;
        }

        if (!$this->isCustomerCommissionEligible($sale)) {
            return 0;
        }

        $rate = self::getSetting('commission.credit_rate');
        $commissionAmount = round($paymentAmount * $rate / 100, 2);

        if ($commissionAmount <= 0) {
            return 0;
        }

        $log = AgentCommissionLog::create([
            'agent_id' => $sale->agent_id,
            'sale_id' => $sale->id,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => $commissionAmount,
            'commission_rate' => $rate,
            'commission_type' => 'credit',
            'description' => "Credit sale commission for #{$sale->invoice_no} (recovered: " . number_format($paymentAmount, 2) . ")",
            'is_paid' => false,
        ]);

        $this->postCommissionLedger($commissionAmount, $log->id, "Credit sale commission accrued - Sale #{$sale->invoice_no}");

        $sale->commission_amount = ($sale->commission_amount ?? 0) + $commissionAmount;
        $sale->commission_due_amount = ($sale->commission_due_amount ?? 0) + $commissionAmount;
        $sale->saveQuietly();

        return $commissionAmount;
    }

    /**
     * New-customer bonus: a fixed, admin-configurable amount (not a random
     * one) once a customer's distinct order count reaches the configured
     * minimum. Fires exactly once per customer.
     */
    public function awardNewCustomerBonus(Customer $customer, Sale $sale)
    {
        $minOrders = self::getSetting('commission.new_customer_bonus_min_orders');

        if (!$customer->is_agent_customer || !$customer->created_by_agent_id) {
            return 0;
        }

        if ($customer->order_count < $minOrders || !$customer->is_active) {
            return 0;
        }

        $alreadyAwarded = AgentCommissionLog::where('reference_type', 'new_customer_bonus')
            ->where('reference_id', $customer->id)
            ->exists();

        if ($alreadyAwarded) {
            return 0;
        }

        $bonusAmount = self::getSetting('commission.new_customer_bonus_amount');

        $log = AgentCommissionLog::create([
            'agent_id' => $customer->created_by_agent_id,
            'sale_id' => $sale->id,
            'reference_type' => 'new_customer_bonus',
            'reference_id' => $customer->id,
            'amount' => $bonusAmount,
            'commission_rate' => 0,
            'commission_type' => 'bonus',
            'description' => "New customer bonus for {$customer->name} ({$minOrders}+ orders, active)",
            'is_paid' => false,
        ]);

        $this->postCommissionLedger($bonusAmount, $log->id, "New customer bonus - {$customer->name}");

        return $bonusAmount;
    }

    /**
     * Recovery bonus: extra % once a sale's recovery rate crosses the
     * configured threshold. Fires exactly once per sale.
     */
    public function awardRecoveryBonus(Sale $sale)
    {
        if (!$sale->agent_id || $sale->is_commission_held) {
            return 0;
        }

        $threshold = self::getSetting('commission.recovery_bonus_threshold_pct');
        if ($sale->recovery_percentage < $threshold) {
            return 0;
        }

        $alreadyAwarded = AgentCommissionLog::where('reference_type', 'recovery_bonus')
            ->where('reference_id', $sale->id)
            ->exists();

        if ($alreadyAwarded) {
            return 0;
        }

        $rate = self::getSetting('commission.recovery_bonus_rate_pct');
        $bonusAmount = round($sale->total_amount * $rate / 100, 2);

        if ($bonusAmount <= 0) {
            return 0;
        }

        $log = AgentCommissionLog::create([
            'agent_id' => $sale->agent_id,
            'sale_id' => $sale->id,
            'reference_type' => 'recovery_bonus',
            'reference_id' => $sale->id,
            'amount' => $bonusAmount,
            'commission_rate' => $rate,
            'commission_type' => 'bonus',
            'description' => "Recovery bonus for #{$sale->invoice_no} ({$sale->recovery_percentage}% recovery)",
            'is_paid' => false,
        ]);

        $this->postCommissionLedger($bonusAmount, $log->id, "Recovery bonus - Sale #{$sale->invoice_no}");

        return $bonusAmount;
    }

    /**
     * Posts the 100%/120%/150% -> configured bonus tiers for every active
     * agent for the given month, once. This used to only ever be computed
     * for on-screen display (Agent\ReportController::target()) and never
     * actually paid - this is what actually pays it.
     */
    public function closeMonthTargetBonuses($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        $tiers = self::getSetting('commission.target_bonus_tiers');
        $results = [];

        $agents = User::where('role', 'sales_agent')->where('is_active', true)->get();

        foreach ($agents as $agent) {
            $targetAmount = $agent->sales_target[$year][$month] ?? 0;

            $achievedAmount = Sale::where('agent_id', $agent->id)
                ->whereIn('status', ['confirmed', 'partial', 'paid'])
                ->whereMonth('sale_date', $month)
                ->whereYear('sale_date', $year)
                ->sum('total_amount');

            $achievementPct = $targetAmount > 0 ? round(($achievedAmount / $targetAmount) * 100, 2) : 0;

            $bonus = 0;
            foreach ($tiers as $tier) {
                if ($achievementPct >= $tier['achievement_pct']) {
                    $bonus = max($bonus, $tier['bonus']);
                }
            }

            $monthlyTarget = AgentMonthlyTarget::updateOrCreate(
                ['agent_id' => $agent->id, 'year' => $year, 'month' => $month],
                [
                    'target_amount' => $targetAmount,
                    'achieved_amount' => $achievedAmount,
                    'achievement_percentage' => $achievementPct,
                    'bonus_earned' => $bonus,
                ]
            );

            if ($bonus > 0) {
                $alreadyLogged = AgentCommissionLog::where('reference_type', 'target_bonus')
                    ->where('reference_id', $monthlyTarget->id)
                    ->exists();

                if (!$alreadyLogged) {
                    $log = AgentCommissionLog::create([
                        'agent_id' => $agent->id,
                        'reference_type' => 'target_bonus',
                        'reference_id' => $monthlyTarget->id,
                        'amount' => $bonus,
                        'commission_rate' => 0,
                        'commission_type' => 'bonus',
                        'description' => "Monthly target bonus for {$monthlyTarget->month_name} {$year} ({$achievementPct}% achieved)",
                        'is_paid' => false,
                    ]);

                    $this->postCommissionLedger($bonus, $log->id, "Monthly target bonus - {$agent->name} - {$monthlyTarget->month_name} {$year}");
                }
            }

            $results[] = ['agent_id' => $agent->id, 'achievement_pct' => $achievementPct, 'bonus' => $bonus];
        }

        return $results;
    }

    /**
     * Reverses commission proportional to a sales return, using the
     * original sale's own recorded commission_amount as the basis (so it
     * works whichever scheme - cash tier or credit rate - actually applied,
     * without trying to re-derive a rate from scratch).
     */
    public function reverseSaleReturnCommission(SalesReturn $salesReturn)
    {
        $sale = $salesReturn->sale;
        if (!$sale || !$sale->agent_id || $sale->total_amount <= 0 || $sale->commission_amount <= 0) {
            return 0;
        }

        // Every other log-creating method in this class guards against
        // double-posting - this one didn't, so any retried/re-triggered
        // call for the same return would dock the agent's commission twice.
        if (AgentCommissionLog::where('reference_type', 'sales_return')->where('reference_id', $salesReturn->id)->exists()) {
            return 0;
        }

        $proportion = min(1, $salesReturn->total_amount / $sale->total_amount);
        $commissionToReverse = round($sale->commission_amount * $proportion, 2);

        if ($commissionToReverse <= 0) {
            return 0;
        }

        $log = AgentCommissionLog::create([
            'agent_id' => $sale->agent_id,
            'sale_id' => $sale->id,
            'reference_type' => 'sales_return',
            'reference_id' => $salesReturn->id,
            'amount' => -$commissionToReverse,
            'commission_rate' => 0,
            'commission_type' => $sale->payment_term,
            'description' => "Sales Return #{$salesReturn->return_no} - Commission Reversal",
            'is_paid' => false,
        ]);

        $this->postCommissionLedger(-$commissionToReverse, $log->id, "Commission reversal - Sales Return #{$salesReturn->return_no}", $salesReturn->return_date ?? null);

        $sale->commission_amount = max(0, $sale->commission_amount - $commissionToReverse);
        $sale->commission_due_amount = max(0, $sale->commission_due_amount - $commissionToReverse);
        $sale->saveQuietly();

        return $commissionToReverse;
    }

    /**
     * Undo the clawback that reverseSaleReturnCommission() posted when a
     * sales return is later deleted (return entered in error, etc). The
     * clawback is always a negative-amount log that's never eligible for
     * payCommission()'s payout allocation (paid_amount(0) can never be
     * "less than" a negative amount), so it's always safe to delete outright
     * along with its ledger entries. Idempotent: no-op if no clawback log
     * exists for this return.
     */
    public function restoreSaleReturnCommission(SalesReturn $salesReturn)
    {
        $log = AgentCommissionLog::where('reference_type', 'sales_return')
            ->where('reference_id', $salesReturn->id)
            ->first();

        if (!$log) {
            return 0;
        }

        $amountToRestore = round(abs((float) $log->amount), 2);

        JournalEntry::where('reference_type', 'sale_commission')->where('reference_id', $log->id)->delete();
        $log->delete();

        $sale = $salesReturn->sale;
        if ($sale) {
            $sale->commission_amount = (float) $sale->commission_amount + $amountToRestore;
            $sale->commission_due_amount = (float) $sale->commission_due_amount + $amountToRestore;
            $sale->saveQuietly();
        }

        return $amountToRestore;
    }

    /**
     * Undo whatever commission has accrued directly on a sale (reference_type
     * 'sale'), because the sale itself is being deleted or its items are
     * being edited/re-priced and commission needs to be recomputed from
     * scratch. Unpaid commission is deleted outright - along with its ledger
     * entries - so a fresh accrual can be posted afterward without tripping
     * the "commission already logged for this sale" guard in
     * calculateCashCommission()/accrueCreditCommission(). Any portion
     * already paid out to the agent is left untouched (real cash already
     * changed hands) but the unpaid remainder is written off with a
     * compensating ledger entry, and the sale is flagged for manual review
     * since an already-paid commission can't be silently reconciled to a
     * changed or removed sale. Idempotent: no-op if the sale has no
     * sale-linked commission logs.
     */
    public function reverseSaleCommission(Sale $sale)
    {
        $logs = AgentCommissionLog::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        $reversedTotal = 0;
        $hadPaidPortion = false;

        foreach ($logs as $log) {
            $paid = (float) $log->paid_amount;

            if ($paid <= 0) {
                JournalEntry::where('reference_type', 'sale_commission')->where('reference_id', $log->id)->delete();
                $reversedTotal += (float) $log->amount;
                $log->delete();
                continue;
            }

            $hadPaidPortion = true;
            $unpaidPortion = round((float) $log->amount - $paid, 2);

            if ($unpaidPortion > 0) {
                $writeOff = AgentCommissionLog::create([
                    'agent_id' => $log->agent_id,
                    'sale_id' => $sale->id,
                    'reference_type' => 'sale_reversal',
                    'reference_id' => $log->id,
                    'amount' => -$unpaidPortion,
                    'commission_rate' => 0,
                    'commission_type' => $log->commission_type,
                    'description' => "Sale #{$sale->invoice_no} removed/edited - writing off unpaid commission",
                    'is_paid' => false,
                ]);

                $this->postCommissionLedger(-$unpaidPortion, $writeOff->id, "Commission write-off - Sale #{$sale->invoice_no} removed/edited");
                $reversedTotal += $unpaidPortion;
            }

            // What was already paid stands exactly as paid - nothing left owed on it.
            $log->amount = $paid;
            $log->is_paid = true;
            $log->save();
        }

        if ($hadPaidPortion) {
            Log::warning('Sale commission partially paid before removal/edit - flagged for manual review', ['sale_id' => $sale->id]);
            $sale->is_commission_held = true;
            $sale->commission_hold_reason = 'Commission was already paid before this sale was edited or removed - review manually.';
        }

        $sale->commission_amount = 0;
        $sale->commission_due_amount = 0;
        $sale->saveQuietly();

        return $reversedTotal;
    }

    /**
     * Debit Agent Commission Expense (5020) / credit Agent Commission
     * Payable (2020) for a positive accrual, or the reverse for a negative
     * write-off/reversal. Tagged by the AgentCommissionLog row's own id
     * (not the sale id) so it never collides with the sale's own
     * revenue/COGS/payment journal entries and each log's ledger effect can
     * be precisely reversed on its own.
     */
    private function postCommissionLedger($amount, $logId, $description, $date = null)
    {
        if (round((float) $amount, 2) == 0) {
            return;
        }

        $expenseAccount = Account::where('code', '5020')->first();
        $payableAccount = Account::where('code', '2020')->first();

        if (!$expenseAccount || !$payableAccount) {
            Log::warning('Commission accounts not found, skipping ledger post', ['log_id' => $logId]);
            return;
        }

        $absAmount = round(abs((float) $amount), 2);

        $entries = $amount > 0
            ? [
                ['account_id' => $expenseAccount->id, 'type' => 'debit', 'amount' => $absAmount, 'description' => $description],
                ['account_id' => $payableAccount->id, 'type' => 'credit', 'amount' => $absAmount, 'description' => $description],
            ]
            : [
                ['account_id' => $payableAccount->id, 'type' => 'debit', 'amount' => $absAmount, 'description' => $description],
                ['account_id' => $expenseAccount->id, 'type' => 'credit', 'amount' => $absAmount, 'description' => $description],
            ];

        $this->postDoubleEntry($entries, 'sale_commission', $logId, $date);
    }

    /**
     * For AccountReconciliationService: repost the 'sale_commission' ledger
     * effect for one AgentCommissionLog row from its own stored amount -
     * that amount is a durable, already-decided fact (unlike a Stock
     * Adjustment's derived cost), so replaying it here is safe. Caller must
     * confirm no journal entries already exist for this log first -
     * postCommissionLedger() itself has no exists-guard.
     */
    public function repostCommissionLedger(AgentCommissionLog $log): void
    {
        $this->postCommissionLedger((float) $log->amount, $log->id, $log->description, $log->created_at);
    }

    public function holdCommission(Sale $sale, $reason)
    {
        $sale->update(['is_commission_held' => true, 'commission_hold_reason' => $reason]);
    }

    public function releaseCommission(Sale $sale)
    {
        $sale->update(['is_commission_held' => false, 'commission_hold_reason' => null]);
    }

    /**
     * Whether a customer has credit sales overdue past their grace period -
     * used to warn (or, if enforce_credit_block is on, block) new credit
     * sales to them. Grace period is the global credit_hold_grace_days
     * setting, unless this customer has their own credit_days set (>0),
     * which then overrides it for this customer only.
     */
    public function checkCreditHoldStatus(Customer $customer)
    {
        $graceDays = (int) $customer->credit_days > 0
            ? (int) $customer->credit_days
            : self::getSetting('commission.credit_hold_grace_days');
        $cutoff = now()->subDays($graceDays);

        $oldestOverdue = Sale::where('customer_id', $customer->id)
            ->where('payment_term', 'credit')
            ->where('due_amount', '>', 0)
            ->where('sale_date', '<', $cutoff)
            ->orderBy('sale_date', 'asc')
            ->first();

        if (!$oldestOverdue) {
            return ['overdue' => false, 'oldest_due_days' => 0, 'block' => false];
        }

        // Carbon 3's diffInDays() defaults to signed (negative, since
        // sale_date is in the past relative to now()) and sub-day-precision
        // float - explicit absolute=true + rounding is required for a plain
        // "N days overdue" figure.
        $daysOverdue = (int) round(now()->diffInDays($oldestOverdue->sale_date, true));

        return [
            'overdue' => true,
            'oldest_due_days' => $daysOverdue,
            'block' => (bool) self::getSetting('commission.enforce_credit_block'),
        ];
    }

    /**
     * Single credit-gate check for a NEW credit sale, called by all three
     * sale-creation entry points (Admin, Agent web, Agent API) right before
     * the sale is created. Returns null when the sale should proceed, or a
     * user-facing message when it should be blocked. Both checks below are
     * admin-toggleable and off by default (enforce_credit_block,
     * enforce_credit_limit) - existing installs see no behavior change
     * until an admin deliberately opts in on the Commission & Bonus
     * settings page.
     */
    public function creditGateMessage(Customer $customer, float $newSaleAmount): ?string
    {
        $holdStatus = $this->checkCreditHoldStatus($customer);
        if ($holdStatus['block']) {
            return "{$customer->name} has a credit sale {$holdStatus['oldest_due_days']} day(s) overdue past their grace period - new credit sales are blocked until it's settled.";
        }

        $creditLimit = (float) $customer->credit_limit;
        if ($creditLimit > 0 && self::getSetting('commission.enforce_credit_limit')) {
            $projectedBalance = (float) $customer->balance + $newSaleAmount;
            if ($projectedBalance > $creditLimit) {
                return "This sale would bring {$customer->name}'s balance to Rs. " . number_format($projectedBalance, 2)
                    . ', over their credit limit of Rs. ' . number_format($creditLimit, 2) . '.';
            }
        }

        return null;
    }

    /**
     * Allocates a lump-sum payment across an agent's unpaid commission
     * logs (oldest first), correctly handling partial payments instead of
     * flipping a whole log to is_paid regardless of the amount paid
     * against it. Records an AgentCommissionPayment for the payout itself.
     */
    public function payCommission(User $agent, $amount, $paymentDate, $method, $referenceNo, $notes, $paidByUserId)
    {
        return DB::transaction(function () use ($agent, $amount, $paymentDate, $method, $referenceNo, $notes, $paidByUserId) {
            AgentCommissionPayment::create([
                'agent_id' => $agent->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'notes' => $notes,
                'paid_by' => $paidByUserId,
            ]);

            $dueLogs = AgentCommissionLog::where('agent_id', $agent->id)
                ->where('is_paid', false)
                ->whereColumn('paid_amount', '<', 'amount')
                ->orderBy('created_at', 'asc')
                ->get();

            $remaining = $amount;

            foreach ($dueLogs as $log) {
                if ($remaining <= 0) {
                    break;
                }

                $due = $log->amount - $log->paid_amount;
                $allocate = min($due, $remaining);

                $log->paid_amount += $allocate;
                if ($log->paid_amount >= $log->amount) {
                    $log->is_paid = true;
                    $log->paid_date = $paymentDate;
                    $log->paid_by = $paidByUserId;
                }
                $log->save();

                if ($log->sale_id) {
                    $relatedSale = Sale::find($log->sale_id);
                    if ($relatedSale) {
                        $relatedSale->commission_paid_amount = (float) $relatedSale->commission_paid_amount + $allocate;
                        $relatedSale->commission_due_amount = max(0, (float) $relatedSale->commission_due_amount - $allocate);
                        $relatedSale->saveQuietly();
                    }
                }

                $remaining -= $allocate;
            }

            $paidOut = $amount - $remaining;

            if ($paidOut > 0) {
                $payableAccount = Account::where('code', '2020')->first();
                $cashAccount = Account::where('code', '1010')->first();
                $bankAccount = Account::where('code', '1020')->first();
                $offsetAccount = $method === 'cash' ? $cashAccount : $bankAccount;

                if ($payableAccount && $offsetAccount) {
                    $this->postDoubleEntry([
                        [
                            'account_id' => $payableAccount->id,
                            'type' => 'debit',
                            'amount' => $paidOut,
                            'description' => "Commission payment to {$agent->name}",
                        ],
                        [
                            'account_id' => $offsetAccount->id,
                            'type' => 'credit',
                            'amount' => $paidOut,
                            'description' => "Commission payment to {$agent->name} via {$method}",
                        ],
                    ], 'commission_payment', $agent->id, $paymentDate);
                } else {
                    Log::warning('Commission payout accounts not found, skipping ledger post', ['agent_id' => $agent->id]);
                }
            }

            return $paidOut;
        });
    }

    // =============================================
    // INTERNAL HELPERS
    // =============================================

    private function rateForCumulative($cumulative, array $tiers)
    {
        foreach ($tiers as $tier) {
            $to = $tier['to'] ?? null;
            if ($to === null || $cumulative <= $to) {
                return $tier['rate'];
            }
        }

        return end($tiers)['rate'] ?? 0;
    }
}

<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Setting;

/**
 * Customer credit-hold / credit-limit policy - extracted out of the old
 * CommissionService (removed along with the Sale Agent module) since this
 * is a general accounts-receivable control, not agent commission. Setting
 * keys keep their legacy 'commission.*' prefix on purpose - renaming them
 * would silently reset whatever an admin already configured.
 */
class CustomerCreditService
{
    private const DEFAULTS = [
        // Outstanding/credit-hold policy.
        'commission.credit_hold_grace_days' => 30,
        'commission.enforce_credit_block' => false,
        // Blocks a new credit sale that would push a customer's balance
        // past their own per-customer credit_limit (0/blank = no limit set).
        'commission.enforce_credit_limit' => false,
    ];

    public static function getSetting($key)
    {
        $default = self::DEFAULTS[$key] ?? null;
        $raw = Setting::get($key);

        if ($raw === null) {
            return $default;
        }

        if (is_bool($default)) {
            return in_array($raw, ['1', 'true', 'yes'], true);
        }

        return is_numeric($raw) ? (float) $raw : $raw;
    }

    public static function setSetting($key, $value)
    {
        $stored = is_bool($value) ? ($value ? '1' : '0') : $value;
        return Setting::set($key, $stored);
    }

    public static function defaults()
    {
        return self::DEFAULTS;
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
     * Single credit-gate check for a NEW credit sale, called right before
     * the sale is created. Returns null when the sale should proceed, or a
     * user-facing message when it should be blocked. Both checks below are
     * admin-toggleable and off by default (enforce_credit_block,
     * enforce_credit_limit) - existing installs see no behavior change
     * until an admin deliberately opts in on the Settings > Credit page.
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
}

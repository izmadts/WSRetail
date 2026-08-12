<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\AgentCommissionLog;
use App\Http\Resources\Api\SaleResource;
use App\Http\Resources\Api\CommissionLogResource;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class ReportController extends ApiController
{
    // Ledger-recognized statuses only - matches the web agent report/dashboard
    // controllers and SaleService::applyStockAndAccounting. A draft sale
    // never posted anything and shouldn't inflate the agent's own totals.
    private const SALE_LEDGER_STATUSES = ['confirmed', 'partial', 'paid'];

    public function overview(Request $request)
    {
        $agent = $this->agent();

        return $this->success([
            'total_customers' => Customer::where('created_by_agent_id', $agent->id)->count(),
            'active_customers' => Customer::where('created_by_agent_id', $agent->id)->where('is_active', true)->count(),
            'total_sales' => (float) Sale::where('agent_id', $agent->id)
                ->whereIn('status', self::SALE_LEDGER_STATUSES)
                ->sum('total_amount'),
            'total_commission' => (float) AgentCommissionLog::where('agent_id', $agent->id)->sum('amount'),
            'monthly_sales' => (float) Sale::where('agent_id', $agent->id)
                ->whereIn('status', self::SALE_LEDGER_STATUSES)
                ->whereMonth('sale_date', now()->month)->whereYear('sale_date', now()->year)
                ->sum('total_amount'),
            'monthly_commission' => (float) AgentCommissionLog::where('agent_id', $agent->id)
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)
                ->sum('amount'),
        ]);
    }

    public function sales(Request $request)
    {
        $agent = $this->agent();
        $query = Sale::where('agent_id', $agent->id)->with('customer');

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $sales = (clone $query)->orderByDesc('sale_date')->paginate($request->input('per_page', 20));

        $summary = [
            'total_amount' => (float) (clone $query)->sum('total_amount'),
            'total_paid' => (float) (clone $query)->sum('paid_amount'),
            'total_due' => (float) (clone $query)->sum('due_amount'),
            'count' => (clone $query)->count(),
        ];

        $response = $this->paginated($sales, fn ($s) => new SaleResource($s));
        $data = $response->getData(true);
        $data['meta']['summary'] = $summary;

        return response()->json($data);
    }

    public function commission(Request $request)
    {
        $agent = $this->agent();
        $query = AgentCommissionLog::where('agent_id', $agent->id)->with('sale');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('reference_type', $request->type);
        }

        $commissions = (clone $query)->orderByDesc('created_at')->paginate($request->input('per_page', 20));

        // Each aggregate clones $query first - chaining straight off the
        // same builder instance would mutate it in place (this is the same
        // bug the web agent report page used to have, where a second
        // ->where('is_paid', ...) AND-ed onto the first and total_due
        // always read 0).
        $summary = [
            'total_earned' => (float) (clone $query)->sum('amount'),
            'total_paid' => (float) (clone $query)->sum('paid_amount'),
            'total_due' => (float) (clone $query)->get()->sum('due_amount'),
            'count' => (clone $query)->count(),
        ];

        $response = $this->paginated($commissions, fn ($c) => new CommissionLogResource($c));
        $data = $response->getData(true);
        $data['meta']['summary'] = $summary;

        return response()->json($data);
    }

    public function target(Request $request)
    {
        $agent = $this->agent();
        $year = $request->input('year', now()->year);

        // Ledger-recognized statuses only, so this preview matches what
        // CommissionService::closeMonthTargetBonuses() will actually pay -
        // matches Agent\ReportController::target() (web).
        $currentMonthSales = (float) Sale::where('agent_id', $agent->id)
            ->whereIn('status', self::SALE_LEDGER_STATUSES)
            ->whereMonth('sale_date', now()->month)->whereYear('sale_date', now()->year)
            ->sum('total_amount');

        $targetAmount = (float) ($agent->sales_target[now()->year][now()->month] ?? 0);
        $achievement = $targetAmount > 0 ? round(($currentMonthSales / $targetAmount) * 100, 2) : 0;

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthSales = (float) Sale::where('agent_id', $agent->id)
                ->whereIn('status', self::SALE_LEDGER_STATUSES)
                ->whereMonth('sale_date', $i)->whereYear('sale_date', $year)
                ->sum('total_amount');

            $monthTarget = (float) ($agent->sales_target[$year][$i] ?? 0);
            $monthAchievement = $monthTarget > 0 ? round(($monthSales / $monthTarget) * 100, 2) : 0;

            $monthlyData[] = [
                'month' => $i,
                'month_name' => date('F', mktime(0, 0, 0, $i, 1)),
                'target' => $monthTarget,
                'achieved' => $monthSales,
                'achievement_pct' => $monthAchievement,
                'bonus' => $this->calculateBonus($monthAchievement),
            ];
        }

        return $this->success([
            'current_month' => [
                'sales' => $currentMonthSales,
                'target' => $targetAmount,
                'achievement_pct' => $achievement,
                'bonus' => $this->calculateBonus($achievement),
            ],
            'monthly_breakdown' => $monthlyData,
        ]);
    }

    /**
     * Reads tiers from the same admin-configurable setting
     * CommissionService::closeMonthTargetBonuses() actually pays from -
     * this used to be hardcoded here (matching an old, already-fixed copy
     * of the same bug in the web Agent\ReportController), so changing the
     * tiers in Settings silently stopped matching what this app showed agents.
     */
    private function calculateBonus($achievementPct)
    {
        $tiers = CommissionService::getSetting('commission.target_bonus_tiers');
        $bonus = 0;

        foreach ($tiers as $tier) {
            if ($achievementPct >= $tier['achievement_pct']) {
                $bonus = max($bonus, $tier['bonus']);
            }
        }

        return $bonus;
    }
}

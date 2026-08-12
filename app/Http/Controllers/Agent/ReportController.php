<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\AgentCommissionLog;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $agent = Auth::user();

        $totalCustomers = Customer::where('created_by_agent_id', $agent->id)->count();
        $activeCustomers = Customer::where('created_by_agent_id', $agent->id)->where('is_active', true)->count();

        $totalSales = Sale::where('agent_id', $agent->id)->sum('total_amount');
        $totalCommission = AgentCommissionLog::where('agent_id', $agent->id)->sum('amount');

        $monthlySales = Sale::where('agent_id', $agent->id)
            ->whereMonth('sale_date', date('m'))
            ->whereYear('sale_date', date('Y'))
            ->sum('total_amount');

        $monthlyCommission = AgentCommissionLog::where('agent_id', $agent->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('amount');

        return view('agent.reports.index', compact(
            'totalCustomers',
            'activeCustomers',
            'totalSales',
            'totalCommission',
            'monthlySales',
            'monthlyCommission'
        ));
    }

    public function sales(Request $request)
    {
        $agent = Auth::user();

        $query = Sale::where('agent_id', $agent->id)->with('customer');

        if ($request->from_date) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(20);

        $summary = [
            'total_amount' => $query->sum('total_amount'),
            'total_paid' => $query->sum('paid_amount'),
            'total_due' => $query->sum('due_amount'),
            'count' => $query->count(),
        ];

        return view('agent.reports.sales', compact('sales', 'summary'));
    }

    public function commission(Request $request)
    {
        $agent = Auth::user();

        $query = AgentCommissionLog::where('agent_id', $agent->id)->with('sale');

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->type && $request->type != 'all') {
            $query->where('reference_type', $request->type);
        }

        $commissions = (clone $query)->orderBy('created_at', 'desc')->paginate(20);

        // Each aggregate clones $query first - chaining ->where() straight
        // off the same builder instance mutates it in place, so a second
        // ->where('is_paid', false) after a first ->where('is_paid', true)
        // would AND them together into a condition that can never match
        // anything (this is why total_due used to always read 0).
        $summary = [
            'total_earned' => (clone $query)->sum('amount'),
            'total_paid' => (clone $query)->sum('paid_amount'),
            'total_due' => (clone $query)->get()->sum('due_amount'),
            'count' => (clone $query)->count(),
        ];

        return view('agent.reports.commission', compact('commissions', 'summary'));
    }

    public function target(Request $request)
    {
        $agent = Auth::user();

        // Get current month's sales - ledger-recognized statuses only, so
        // this preview matches what CommissionService::closeMonthTargetBonuses()
        // will actually pay (a draft sale created just to inflate month-to-date
        // totals must not count toward a real bonus).
        $saleLedgerStatuses = ['confirmed', 'partial', 'paid'];

        $currentMonthSales = Sale::where('agent_id', $agent->id)
            ->whereIn('status', $saleLedgerStatuses)
            ->whereMonth('sale_date', date('m'))
            ->whereYear('sale_date', date('Y'))
            ->sum('total_amount');

        $targetAmount = $agent->sales_target[date('Y')][date('m')] ?? 0;
        $achievement = $targetAmount > 0 ? ($currentMonthSales / $targetAmount) * 100 : 0;

        $bonus = $this->calculateBonus($achievement);

        // Get monthly breakdown
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthSales = Sale::where('agent_id', $agent->id)
                ->whereIn('status', $saleLedgerStatuses)
                ->whereMonth('sale_date', $i)
                ->whereYear('sale_date', date('Y'))
                ->sum('total_amount');

            $monthTarget = $agent->sales_target[date('Y')][$i] ?? 0;
            $monthAchievement = $monthTarget > 0 ? ($monthSales / $monthTarget) * 100 : 0;

            $monthlyData[] = [
                'month' => date('F', mktime(0, 0, 0, $i, 1)),
                'target' => $monthTarget,
                'achieved' => $monthSales,
                'achievement' => $monthAchievement,
                'bonus' => $this->calculateBonus($monthAchievement),
            ];
        }

        return view('agent.reports.target', compact(
            'currentMonthSales',
            'targetAmount',
            'achievement',
            'bonus',
            'monthlyData'
        ));
    }

    /**
     * Reads tiers from the same admin-configurable setting
     * CommissionService::closeMonthTargetBonuses() actually pays from -
     * these used to be hardcoded here (and duplicated in
     * AgentMonthlyTarget::calculateBonus()), so changing the tiers in
     * Settings silently stopped matching what this preview showed agents.
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
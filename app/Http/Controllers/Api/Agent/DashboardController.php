<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\AgentCommissionLog;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    /**
     * Same figures as the web agent dashboard (resources/views/agent/dashboard.blade.php),
     * reshaped as JSON for the mobile app's home screen.
     */
    public function index(Request $request)
    {
        $agent = $this->agent();

        // Ledger-recognized statuses only - matches the web agent dashboard
        // (Agent\DashboardController) and SaleService::applyStockAndAccounting.
        // A draft sale never posted anything and shouldn't inflate the
        // agent's own totals shown on this screen.
        $saleLedgerStatuses = ['confirmed', 'partial', 'paid'];

        $sales = Sale::where('agent_id', $agent->id)->whereIn('status', $saleLedgerStatuses)->get();

        $currentMonthSales = Sale::where('agent_id', $agent->id)
            ->whereIn('status', $saleLedgerStatuses)
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->get();

        $totalSales = (float) $sales->sum('total_amount');
        $totalPaid = (float) $sales->sum('paid_amount');
        $totalDue = (float) $sales->sum('due_amount');
        $recoveryRate = $totalSales > 0 ? round(($totalPaid / $totalSales) * 100, 2) : 0;

        $commissionByType = function (string $type) use ($agent) {
            return (float) AgentCommissionLog::where('agent_id', $agent->id)
                ->where('reference_type', $type)
                ->sum('amount');
        };

        $monthlyLabels = [];
        $monthlySalesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyLabels[] = $date->format('M');
            $monthlySalesData[] = (float) Sale::where('agent_id', $agent->id)
                ->whereIn('status', $saleLedgerStatuses)
                ->whereMonth('sale_date', $date->month)
                ->whereYear('sale_date', $date->year)
                ->sum('total_amount');
        }

        return $this->success([
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'total_transactions' => $sales->count(),
            'recovery_rate' => $recoveryRate,
            'current_month' => [
                'total_amount' => (float) $currentMonthSales->sum('total_amount'),
                'commission' => (float) $currentMonthSales->sum('commission_amount'),
                'paid' => (float) $currentMonthSales->sum('paid_amount'),
            ],
            'commission_breakdown' => [
                'sale_commission' => $commissionByType('sale'),
                'new_customer_bonus' => $commissionByType('new_customer_bonus'),
                'recovery_bonus' => $commissionByType('recovery_bonus'),
                'target_bonus' => $commissionByType('target_bonus'),
            ],
            'total_commission' => (float) AgentCommissionLog::where('agent_id', $agent->id)->sum('amount'),
            'total_customers' => Customer::where('created_by_agent_id', $agent->id)->count(),
            'new_customers_this_month' => Customer::where('created_by_agent_id', $agent->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'monthly_sales_chart' => [
                'labels' => $monthlyLabels,
                'data' => $monthlySalesData,
            ],
        ]);
    }
}

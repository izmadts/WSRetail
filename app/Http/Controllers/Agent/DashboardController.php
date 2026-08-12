<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\AgentCommissionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $agent = Auth::user();
        
        // Get agent's customers
        $customers = Customer::where('created_by_agent_id', $agent->id)->get();
        
        // Get agent's sales - ledger-recognized statuses only (matches
        // SaleService::applyStockAndAccounting); a draft never posted
        // anything and shouldn't inflate the agent's own totals.
        $saleLedgerStatuses = ['confirmed', 'partial', 'paid'];
        $sales = Sale::where('agent_id', $agent->id)->whereIn('status', $saleLedgerStatuses)->get();

        // Current month stats
        $currentMonthSales = Sale::where('agent_id', $agent->id)
            ->whereIn('status', $saleLedgerStatuses)
            ->whereMonth('sale_date', date('m'))
            ->whereYear('sale_date', date('Y'))
            ->get();

        $currentMonthTotal = $currentMonthSales->sum('total_amount');
        $currentMonthPaid = $currentMonthSales->sum('paid_amount');

        // Total stats
        $totalSales = $sales->sum('total_amount');
        $totalPaid = $sales->sum('paid_amount');
        $totalDue = $sales->sum('due_amount');

        // Recovery rate
        $recoveryRate = $totalSales > 0 ? ($totalPaid / $totalSales) * 100 : 0;

        // Commission always comes from AgentCommissionLog (matches the
        // Commission and Report pages below) - summing Sale.commission_amount
        // instead used to show this page a lower total than every other
        // commission page for the same agent, since it silently excluded
        // new-customer/recovery/target bonuses.
        $totalCommission = AgentCommissionLog::where('agent_id', $agent->id)->sum('amount');
        $currentMonthCommission = AgentCommissionLog::where('agent_id', $agent->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->sum('amount');
        
        // Commission breakdown
        $saleCommission = AgentCommissionLog::where('agent_id', $agent->id)
            ->where('reference_type', 'sale')
            ->sum('amount');
        
        $newCustomerBonus = AgentCommissionLog::where('agent_id', $agent->id)
            ->where('reference_type', 'new_customer_bonus')
            ->sum('amount');
        
        $recoveryBonus = AgentCommissionLog::where('agent_id', $agent->id)
            ->where('reference_type', 'recovery_bonus')
            ->sum('amount');
        
        $targetBonus = AgentCommissionLog::where('agent_id', $agent->id)
            ->where('reference_type', 'target_bonus')
            ->sum('amount');
        
        // Commission logs
        $commissionLogs = AgentCommissionLog::where('agent_id', $agent->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // New customers this month
        $newCustomers = Customer::where('created_by_agent_id', $agent->id)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->count();
        
        // =============================================
        // ✅ CHART DATA: Monthly Sales (Last 6 Months)
        // =============================================
        $monthlyLabels = [];
        $monthlySalesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));
            $monthName = date('M', strtotime("-$i months"));

            $monthlyLabels[] = $monthName;
            
            $salesAmount = Sale::where('agent_id', $agent->id)
                ->whereIn('status', $saleLedgerStatuses)
                ->whereMonth('sale_date', $month)
                ->whereYear('sale_date', $year)
                ->sum('total_amount');
            
            $monthlySalesData[] = round($salesAmount, 2);
        }

        return view('agent.dashboard', compact(
            'agent',
            'customers',
            'sales',
            'currentMonthTotal',
            'currentMonthCommission',
            'currentMonthPaid',
            'totalSales',
            'totalCommission',
            'totalPaid',
            'totalDue',
            'recoveryRate',
            'saleCommission',
            'newCustomerBonus',
            'recoveryBonus',
            'targetBonus',
            'commissionLogs',
            'newCustomers',
            'monthlyLabels',
            'monthlySalesData'
        ));
    }
}
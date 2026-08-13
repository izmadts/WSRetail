<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // =============================================
        // STATS
        // =============================================
        $data = [
            'totalProducts' => Product::count(),
            'totalCategories' => Category::count(),
            'lowStockProducts' => Product::lowStock()->count(),
            'activeProducts' => Product::active()->count(),
        ];

        // =============================================
        // SALES DATA
        // =============================================
        // Ledger-recognized statuses only (matches SaleService::
        // applyStockAndAccounting) - otherwise this figure includes drafts
        // that never posted anything, and disagrees with every report
        // that's filtered by status.
        $saleLedgerStatuses = ['confirmed', 'partial', 'paid'];
        $purchaseLedgerStatuses = ['received', 'partial', 'paid'];

        $currentMonthSales = Sale::whereMonth('sale_date', date('m'))
            ->whereYear('sale_date', date('Y'))
            ->whereIn('status', $saleLedgerStatuses)
            ->sum('total_amount');

        // In January, "last month" is December of the PREVIOUS year - using
        // the current year here always compared against a December that
        // hadn't happened yet, making growth% wrong every January.
        $previousMonthSales = Sale::whereMonth('sale_date', date('m', strtotime('-1 month')))
            ->whereYear('sale_date', date('Y', strtotime('-1 month')))
            ->whereIn('status', $saleLedgerStatuses)
            ->sum('total_amount');

        $salesGrowth = 0;
        if ($previousMonthSales > 0) {
            $salesGrowth = (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100;
        }

        $currentMonthPurchases = Purchase::whereMonth('purchase_date', date('m'))
            ->whereYear('purchase_date', date('Y'))
            ->whereIn('status', $purchaseLedgerStatuses)
            ->sum('total_amount');

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();

        // =============================================
        // CHART DATA: Monthly Sales & Purchases (Last 12 Months)
        // =============================================
        $monthlySales = [];
        $monthlyPurchases = [];
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));
            $monthName = date('M', strtotime("-$i months"));

            $months[] = $monthName;

            $sales = Sale::whereMonth('sale_date', $month)
                ->whereYear('sale_date', $year)
                ->whereIn('status', $saleLedgerStatuses)
                ->sum('total_amount');
            $monthlySales[] = round($sales, 2);

            $purchases = Purchase::whereMonth('purchase_date', $month)
                ->whereYear('purchase_date', $year)
                ->whereIn('status', $purchaseLedgerStatuses)
                ->sum('total_amount');
            $monthlyPurchases[] = round($purchases, 2);
        }

        // =============================================
        // CHART DATA: Top Products
        // =============================================
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sales.status', $saleLedgerStatuses)
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // =============================================
        // CHART DATA: Payment Status
        // =============================================
        $paidSales = Sale::where('status', 'paid')->sum('total_amount');
        $partialSales = Sale::where('status', 'partial')->sum('total_amount');
        $pendingSales = Sale::where('status', 'confirmed')->sum('total_amount');
        $draftSales = Sale::where('status', 'draft')->sum('total_amount');

        // =============================================
        // CHART DATA: Daily Sales (Last 30 Days)
        // =============================================
        $dailySales = [];
        $dailyLabels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $label = date('d M', strtotime("-$i days"));

            $dailyLabels[] = $label;
            $sales = Sale::whereDate('sale_date', $date)->whereIn('status', $saleLedgerStatuses)->sum('total_amount');
            $dailySales[] = round($sales, 2);
        }

        // =============================================
        // RECENT ACTIVITIES
        // =============================================
        $recentSales = Sale::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPurchases = Purchase::with('supplier')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', array_merge($data, [
            'currentMonthSales' => $currentMonthSales,
            'previousMonthSales' => $previousMonthSales,
            'salesGrowth' => $salesGrowth,
            'currentMonthPurchases' => $currentMonthPurchases,
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'months' => $months,
            'monthlySales' => $monthlySales,
            'monthlyPurchases' => $monthlyPurchases,
            'topProducts' => $topProducts,
            'paidSales' => $paidSales,
            'partialSales' => $partialSales,
            'pendingSales' => $pendingSales,
            'draftSales' => $draftSales,
            'dailyLabels' => $dailyLabels,
            'dailySales' => $dailySales,
            'recentSales' => $recentSales,
            'recentPurchases' => $recentPurchases,
        ]));
    }
}
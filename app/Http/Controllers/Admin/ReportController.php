<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use App\Helpers\LedgerHelper;
use Barryvdh\DomPDF\Facade\Pdf;


class ReportController extends Controller
{

    public function profitLoss(Request $request)
    {
        // Get date range
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');

        // =============================================
        // 1. INCOME (Revenue)
        // =============================================

        // Sales Revenue - matches SaleService::applyStockAndAccounting(),
        // which posts revenue as soon as a sale is confirmed, not just once
        // it's fully paid.
        $salesRevenue = Sale::whereBetween('sale_date', [$fromDate, $toDate])
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->sum('total_amount');

        // Netted against returns processed in the period, matching what
        // SalesReturn::reverseAccounting() already does to the real ledger
        // (debits Revenue back down) - without this, a returned sale kept
        // counting its original full amount here, so this report and the
        // Trial Balance (which reads the real ledger, see trialBalance()
        // below) could disagree by exactly the returned amount. Confirmed
        // live: a Rs. 900 return left this report showing Rs. 900 more
        // revenue than the ledger did for the same period.
        $salesReturns = SalesReturn::whereBetween('return_date', [$fromDate, $toDate])->sum('total_amount');
        $salesRevenue -= $salesReturns;

        // Other Income (from income module)
        $otherIncome = Income::whereBetween('income_date', [$fromDate, $toDate])
            ->sum('amount');

        $totalIncome = $salesRevenue + $otherIncome;

        // =============================================
        // 2. COST OF GOODS SOLD (COGS)
        // =============================================

        // Cost of goods actually SOLD in the period (matches what
        // SaleService::postAccounting() posts to ledger account 5010) - not
        // the cost of goods purchased, which is an unrelated figure that
        // happens to share the "COGS" label. Falls back to the product's
        // current purchase_price for older line items sold before unit_cost
        // started being captured.
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$fromDate, $toDate])
            ->whereIn('sales.status', ['confirmed', 'partial', 'paid'])
            ->sum(DB::raw('sale_items.quantity * COALESCE(sale_items.unit_cost, products.purchase_price)'));

        // Netted against the COGS of returned items, matching
        // SalesReturn::reverseAccounting() reversing COGS in the real
        // ledger at the same unit_cost snapshot it was originally posted
        // at (not the product's current cost, which may have drifted).
        $returnedCogs = DB::table('sales_return_items')
            ->join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->join('sale_items', 'sales_return_items.sale_item_id', '=', 'sale_items.id')
            ->join('products', 'sales_return_items.product_id', '=', 'products.id')
            ->whereBetween('sales_returns.return_date', [$fromDate, $toDate])
            ->sum(DB::raw('sales_return_items.quantity * COALESCE(sale_items.unit_cost, products.purchase_price)'));
        $cogs -= $returnedCogs;

        // =============================================
        // 3. GROSS PROFIT
        // =============================================

        $grossProfit = $totalIncome - $cogs;

        // =============================================
        // 4. EXPENSES
        // =============================================

        // Operating Expenses (from expense module)
        $operatingExpenses = Expense::whereBetween('expense_date', [$fromDate, $toDate])
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        // =============================================
        // 5. NET PROFIT / LOSS
        // =============================================

        $netProfit = $grossProfit - $operatingExpenses;

        // =============================================
        // 6. BREAKDOWN BY CATEGORY
        // =============================================

        // Income by category
        $incomeByCategory = Income::whereBetween('income_date', [$fromDate, $toDate])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // Expenses by category
        $expensesByCategory = Expense::whereBetween('expense_date', [$fromDate, $toDate])
            ->whereIn('status', ['approved', 'paid'])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // =============================================
        // 7. MONTHLY BREAKDOWN
        // =============================================

        // Iterating with a raw month integer (11, 12, 01, 02...) and a
        // single fixed $year meant any range crossing a year boundary
        // (e.g. Nov-Feb) had startMonth > endMonth and the loop never ran
        // at all, silently rendering an empty monthly breakdown. Carbon's
        // addMonth() rolls the year over correctly.
        $monthlyData = [];
        $cursor = Carbon::parse($fromDate)->startOfMonth();
        $periodEnd = Carbon::parse($toDate)->startOfMonth();

        while ($cursor->lte($periodEnd)) {
            $monthStart = $cursor->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $cursor->copy()->endOfMonth()->format('Y-m-d');

            $monthlySales = Sale::whereBetween('sale_date', [$monthStart, $monthEnd])
                ->whereIn('status', ['confirmed', 'partial', 'paid'])
                ->sum('total_amount');
            $monthlySales -= SalesReturn::whereBetween('return_date', [$monthStart, $monthEnd])->sum('total_amount');

            $monthlyExpenses = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');

            $monthlyData[] = [
                'month' => $cursor->format('M Y'),
                'income' => $monthlySales,
                'expenses' => $monthlyExpenses,
                'profit' => $monthlySales - $monthlyExpenses,
            ];

            $cursor->addMonth();
        }

        return view('admin.reports.profit-loss', compact(
            'fromDate',
            'toDate',
            'totalIncome',
            'salesRevenue',
            'otherIncome',
            'cogs',
            'grossProfit',
            'operatingExpenses',
            'netProfit',
            'incomeByCategory',
            'expensesByCategory',
            'monthlyData'
        ));
    }
    // =============================================
    // 1. CUSTOMER REPORTS
    // =============================================

    public function customers(Request $request)
    {
        $query = Customer::withCount('sales');

        // Filter by status
        if ($request->status && $request->status != 'all') {
            $query->where('is_active', $request->status == 'active');
        }

        // Filter by city
        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filter by date range
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $customers = $query->orderBy('name')->get();

        // Summary
        $totalCustomers = $customers->count();
        $activeCustomers = $customers->where('is_active', true)->count();
        $totalSales = $customers->sum('total_sales');
        $totalBalance = $customers->sum('balance');

        return view('admin.reports.customers', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'totalSales',
            'totalBalance'
        ));
    }

    public function customerDetail(Customer $customer)
    {
        $customer->load(['sales' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'salePayments']);

        return view('admin.reports.customer-detail', compact('customer'));
    }
   
    // =============================================
    // 2. SUPPLIER REPORTS
    // =============================================

    /**
     * Suppliers Report
     */
    public function suppliers(Request $request)
    {
        $query = Supplier::withCount('purchases');

        if ($request->status && $request->status != 'all') {
            $query->where('is_active', $request->status == 'active');
        }

        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $suppliers = $query->orderBy('name')->get();

        // total_purchases/total_paid/total_due/balance all come from
        // Supplier's own accessors (Supplier.php) - not recomputed here.
        // They used to be overwritten with unfiltered values on these exact
        // model instances, which Eloquent silently discarded the moment
        // they were read back (since accessor methods with those same names
        // already existed), except for total_due, which has no accessor -
        // so it kept the raw, unfiltered value while its neighbors quietly
        // reverted to the filtered one. That mismatch is what let a
        // supplier's "Total Due" show as larger than their "Total
        // Purchases" on the same report row.
        $totalSuppliers = $suppliers->count();
        $activeSuppliers = $suppliers->where('is_active', true)->count();
        $totalPurchases = $suppliers->sum('total_purchases');
        $totalPaid = $suppliers->sum('total_paid');
        $totalDue = $suppliers->sum('total_due');
        $totalBalance = $suppliers->sum('balance');

        return view('admin.reports.suppliers', compact(
            'suppliers',
            'totalSuppliers',
            'activeSuppliers',
            'totalPurchases',
            'totalPaid',
            'totalDue',
            'totalBalance'
        ));
    }


    /**
     * Supplier Detail
     */
    public function supplierDetail(Supplier $supplier)
    {
        $supplier->load(['purchases' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'purchasePayments']);

        // Read straight off the model's own accessors - previously
        // recomputed from scratch with no status/payment_term filter at
        // all, so this page could show a different balance than the
        // suppliers list and export for the same supplier.
        $totalPurchases = $supplier->total_purchases;
        $totalPaid = $supplier->total_paid;
        $totalDue = $supplier->total_due;
        $balance = $supplier->balance;

        return view('admin.reports.supplier-detail', compact(
            'supplier',
            'totalPurchases',
            'totalPaid',
            'totalDue',
            'balance'
        ));
    }

    // =============================================
    // 3. RECEIVABLE REPORT (Customer Outstanding)
    // =============================================

    public function receivable(Request $request)
    {
        $query = Customer::with('sales', 'salePayments');

        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $customers = $query->orderBy('name')->get();

        // Filtered/sorted entirely off Customer::balance (the same accessor
        // used everywhere else a customer's balance is shown) - it used to
        // be prefiltered by a separate raw-SQL formula with no status filter
        // on sales, so the min_balance threshold typed here didn't
        // necessarily match what actually ended up on screen.
        $minBalance = $request->filled('min_balance') ? (float) $request->min_balance : null;

        $customersWithBalance = $customers->filter(function ($customer) use ($minBalance) {
            if ($customer->balance <= 0) {
                return false;
            }
            return $minBalance === null || $customer->balance >= $minBalance;
        })->sortByDesc('balance');

        $totalReceivable = $customersWithBalance->sum('balance');
        $totalCustomers = $customersWithBalance->count();
        $avgReceivable = $totalCustomers > 0 ? $totalReceivable / $totalCustomers : 0;

        return view('admin.reports.receivable', compact(
            'customersWithBalance',
            'totalReceivable',
            'totalCustomers',
            'avgReceivable'
        ));
    }

    // =============================================
    // 4. EXPENSE REPORTS
    // =============================================

    public function expenses(Request $request)
    {
        $query = Expense::with('category');

        if ($request->from_date) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status != 'all') {
                $query->where('status', $request->status);
            }
            // status == 'all' is an explicit, deliberate choice to include
            // pending/cancelled expenses too.
        } else {
            // Default view matches what the P&L's operating-expenses figure
            // counts (approved/paid only) - pending expenses haven't
            // actually posted to the ledger yet.
            $query->whereIn('status', ['approved', 'paid']);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $totalExpenses = $expenses->sum('amount');
        $byCategory = $expenses->groupBy('category_id')->map(function ($items) {
            return [
                'category' => $items->first()->category->name ?? 'Uncategorized',
                'total' => $items->sum('amount'),
                'count' => $items->count()
            ];
        });

        $categories = \App\Models\ExpenseCategory::active()->get();

        return view('admin.reports.expenses', compact(
            'expenses',
            'totalExpenses',
            'byCategory',
            'categories'
        ));
    }

    // =============================================
    // 5. INCOME REPORTS
    // =============================================

    public function incomes(Request $request)
    {
        $query = Income::with('category');

        if ($request->from_date) {
            $query->whereDate('income_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('income_date', '<=', $request->to_date);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->source && $request->source != 'all') {
            $query->where('source', $request->source);
        }

        $incomes = $query->orderBy('income_date', 'desc')->get();

        $totalIncome = $incomes->sum('amount');
        $byCategory = $incomes->groupBy('category_id')->map(function ($items) {
            return [
                'category' => $items->first()->category->name ?? 'Uncategorized',
                'total' => $items->sum('amount'),
                'count' => $items->count()
            ];
        });

        $categories = \App\Models\IncomeCategory::active()->get();

        return view('admin.reports.incomes', compact(
            'incomes',
            'totalIncome',
            'byCategory',
            'categories'
        ));
    }

    // =============================================
    // 7. DAILY SUMMARY REPORT
    // =============================================

    public function dailySummary(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        // Only sales/purchases the ledger actually recognized for the day -
        // an unconfirmed draft (or a cancelled one) never posted anything
        // and must not inflate the day's totals.
        $sales = Sale::whereDate('sale_date', $date)->whereIn('status', ['confirmed', 'partial', 'paid'])->get();
        $purchases = Purchase::whereDate('purchase_date', $date)->whereIn('status', ['received', 'partial', 'paid'])->get();
        $expenses = Expense::whereDate('expense_date', $date)->whereIn('status', ['approved', 'paid'])->get();
        $incomes = Income::whereDate('income_date', $date)->get();

        $summary = [
            'date' => $date,
            'total_sales' => $sales->sum('total_amount'),
            'sales_count' => $sales->count(),
            'total_purchases' => $purchases->sum('total_amount'),
            'purchases_count' => $purchases->count(),
            'total_expenses' => $expenses->sum('amount'),
            'expenses_count' => $expenses->count(),
            'total_income' => $incomes->sum('amount'),
            'income_count' => $incomes->count(),
            'net_profit' => $sales->sum('total_amount') + $incomes->sum('amount') - $purchases->sum('total_amount') - $expenses->sum('amount'),
        ];

        return view('admin.reports.daily-summary', compact('summary'));
    }

    // =============================================
    // 8. TAX REPORT
    // =============================================

    public function taxReport(Request $request)
    {
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');

        $sales = Sale::whereBetween('sale_date', [$fromDate, $toDate])
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->get();

        $purchases = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])
            ->whereIn('status', ['received', 'partial', 'paid'])
            ->get();

        $salesTax = $sales->sum('tax');
        $purchaseTax = $purchases->sum('tax');
        $netTax = $salesTax - $purchaseTax;

        return view('admin.reports.tax', compact(
            'fromDate',
            'toDate',
            'sales',
            'purchases',
            'salesTax',
            'purchaseTax',
            'netTax'
        ));
    }

    public function taxReportPdf(Request $request)
    {
        $view = $this->taxReport($request);
        $pdf = Pdf::loadView('admin.exports.tax-pdf', $view->getData());
        return $pdf->download('tax-report-' . date('Y-m-d') . '.pdf');
    }
    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request)
    {
        // Get date range
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');

        // =============================================
        // 1. GET ALL ACCOUNTS WITH BALANCES
        // =============================================
        
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        $trialBalance = [];

        foreach ($accounts as $account) {
            // A trial balance is cumulative as of a date, not a date-range
            // movement report - it must match Account::getBalanceAttribute()
            // (used everywhere else an account's balance is shown), which
            // sums every entry ever posted with no lower bound. Bounding
            // this by $fromDate as well used to make it silently disagree
            // with the Chart of Accounts page for the same account/day.
            $totalDebits = JournalEntry::where('account_id', $account->id)
                ->where('type', 'debit')
                ->where('entry_date', '<=', $toDate)
                ->sum('amount');

            // Calculate total credits for this account
            $totalCredits = JournalEntry::where('account_id', $account->id)
                ->where('type', 'credit')
                ->where('entry_date', '<=', $toDate)
                ->sum('amount');

            // Calculate net balance
            $balance = 0;
            $balanceType = '';

            if ($account->normal_balance == 'Debit') {
                $balance = $totalDebits - $totalCredits;
                $balanceType = $balance >= 0 ? 'debit' : 'credit';
            } else {
                $balance = $totalCredits - $totalDebits;
                $balanceType = $balance >= 0 ? 'credit' : 'debit';
            }

            // Only include accounts with balance > 0
            if (abs($balance) > 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'normal_balance' => $account->normal_balance,
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'balance' => abs($balance),
                    'balance_type' => $balanceType,
                ];
            }
        }

        // =============================================
        // 2. CALCULATE TOTALS
        // =============================================
        
        $totalDebitBalance = collect($trialBalance)
            ->where('balance_type', 'debit')
            ->sum('balance');

        $totalCreditBalance = collect($trialBalance)
            ->where('balance_type', 'credit')
            ->sum('balance');

        // =============================================
        // 3. GROUP BY ACCOUNT TYPE
        // =============================================
        
        $groupedByType = collect($trialBalance)->groupBy('type')->map(function($items) {
            return [
                'debit' => $items->where('balance_type', 'debit')->sum('balance'),
                'credit' => $items->where('balance_type', 'credit')->sum('balance'),
                'count' => $items->count(),
            ];
        });

        return view('admin.reports.trial-balance', compact(
            'fromDate',
            'toDate',
            'trialBalance',
            'totalDebitBalance',
            'totalCreditBalance',
            'groupedByType'
        ));
    }

    public function trialBalancePdf(Request $request)
    {
        $view = $this->trialBalance($request);
        $pdf = Pdf::loadView('admin.exports.trial-balance-pdf', $view->getData());
        return $pdf->download('trial-balance-' . date('Y-m-d') . '.pdf');
    }

    public function profitLossPdf(Request $request)
    {
        $view = $this->profitLoss($request);
        $pdf = Pdf::loadView('admin.exports.profit-loss-pdf', $view->getData());
        return $pdf->download('profit-loss-' . date('Y-m-d') . '.pdf');
    }

    public function receivablePdf(Request $request)
    {
        $view = $this->receivable($request);
        $pdf = Pdf::loadView('admin.exports.receivable-pdf', $view->getData());
        return $pdf->download('receivable-' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    // 9. PAYABLE REPORT (Supplier Outstanding) - mirrors receivable()
    // =============================================

    public function payable(Request $request)
    {
        $query = Supplier::with('purchases', 'purchasePayments');

        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $suppliers = $query->orderBy('name')->get();

        $minBalance = $request->filled('min_balance') ? (float) $request->min_balance : null;

        $suppliersWithBalance = $suppliers->filter(function ($supplier) use ($minBalance) {
            if ($supplier->balance <= 0) {
                return false;
            }
            return $minBalance === null || $supplier->balance >= $minBalance;
        })->sortByDesc('balance');

        $totalPayable = $suppliersWithBalance->sum('balance');
        $totalSuppliers = $suppliersWithBalance->count();
        $avgPayable = $totalSuppliers > 0 ? $totalPayable / $totalSuppliers : 0;

        return view('admin.reports.payable', compact(
            'suppliersWithBalance',
            'totalPayable',
            'totalSuppliers',
            'avgPayable'
        ));
    }

    public function payablePdf(Request $request)
    {
        $view = $this->payable($request);
        $pdf = Pdf::loadView('admin.exports.payable-pdf', $view->getData());
        return $pdf->download('payable-' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    // 10. CUSTOMER LEDGER (khata-style running balance)
    // =============================================

    /**
     * No payment_term filter, unlike the supplier side below - a cash sale's
     * own instant full payment (SaleService::recordPayment, fired in the
     * same request the sale is created) shows up as its own debit+credit
     * pair that nets to zero, which is both the correct running balance AND
     * more informative than silently omitting cash sales from the ledger.
     * Matches Customer::getTotalSalesAttribute()'s own no-payment_term-filter
     * convention, so this ledger's closing balance always agrees with the
     * balance figure shown everywhere else for this customer.
     */
    private function customerLedgerData(Customer $customer, ?string $fromDate, ?string $toDate): array
    {
        $balanceStatuses = ['confirmed', 'partial', 'paid'];

        $rows = [];

        foreach (Sale::where('customer_id', $customer->id)->whereIn('status', $balanceStatuses)->get() as $sale) {
            $rows[] = [
                'date' => $sale->sale_date,
                'particulars' => "Sale Invoice #{$sale->invoice_no}",
                'reference' => $sale->invoice_no,
                'debit' => (float) $sale->total_amount,
                'credit' => 0,
            ];
        }

        foreach ($customer->salePayments()->whereHas('sale')->get() as $payment) {
            $rows[] = [
                'date' => $payment->payment_date,
                'particulars' => 'Payment Received (' . ucfirst(str_replace('_', ' ', $payment->payment_method)) . ')',
                'reference' => $payment->reference_no,
                'debit' => 0,
                'credit' => (float) $payment->amount,
            ];
        }

        foreach ($customer->payments as $payment) {
            $rows[] = [
                'date' => $payment->payment_date,
                'particulars' => 'Direct Payment Received (' . ucfirst(str_replace('_', ' ', $payment->payment_method)) . ')',
                'reference' => $payment->reference_no,
                'debit' => 0,
                'credit' => (float) $payment->amount,
            ];
        }

        foreach (SalesReturn::where('customer_id', $customer->id)->get() as $return) {
            $rows[] = [
                'date' => $return->return_date,
                'particulars' => "Sales Return #{$return->return_no}",
                'reference' => $return->return_no,
                'debit' => 0,
                'credit' => (float) $return->total_amount,
            ];
        }

        return $this->buildPeriodLedger($rows, (float) $customer->opening_balance, $fromDate, $toDate) + ['customer' => $customer];
    }

    public function customerLedger(Customer $customer, Request $request)
    {
        return view('admin.reports.customer-ledger', $this->customerLedgerData($customer, $request->from_date, $request->to_date));
    }

    public function customerLedgerPdf(Customer $customer, Request $request)
    {
        $data = $this->customerLedgerData($customer, $request->from_date, $request->to_date);
        $pdf = Pdf::loadView('admin.exports.customer-ledger-pdf', $data);
        return $pdf->download('customer-ledger-' . $customer->code . '-' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    // 11. SUPPLIER LEDGER (khata-style running balance)
    // =============================================

    /**
     * Filtered to payment_term='credit' throughout, matching
     * Supplier::total_purchases/total_paid/total_returned's own established
     * rule that a cash purchase settles instantly and never opens a payable
     * - keeping this ledger's closing balance consistent with the balance
     * shown on the supplier's own profile and the Payable report above.
     */
    private function supplierLedgerData(Supplier $supplier, ?string $fromDate, ?string $toDate): array
    {
        $rows = [];

        foreach (Purchase::where('supplier_id', $supplier->id)->whereIn('status', ['received', 'partial', 'paid'])->where('payment_term', 'credit')->get() as $purchase) {
            $rows[] = [
                'date' => $purchase->purchase_date,
                'particulars' => "Purchase Invoice #{$purchase->invoice_no}",
                'reference' => $purchase->invoice_no,
                'debit' => 0,
                'credit' => (float) $purchase->total_amount,
            ];
        }

        foreach ($supplier->purchasePayments()->whereHas('purchase', fn ($q) => $q->where('payment_term', 'credit'))->get() as $payment) {
            $rows[] = [
                'date' => $payment->payment_date,
                'particulars' => 'Payment Made (' . ucfirst(str_replace('_', ' ', $payment->payment_method)) . ')',
                'reference' => $payment->reference_no,
                'debit' => (float) $payment->amount,
                'credit' => 0,
            ];
        }

        foreach ($supplier->payments as $payment) {
            $rows[] = [
                'date' => $payment->payment_date,
                'particulars' => 'Direct Payment Made (' . ucfirst(str_replace('_', ' ', $payment->payment_method)) . ')',
                'reference' => $payment->reference_no,
                'debit' => (float) $payment->amount,
                'credit' => 0,
            ];
        }

        foreach (PurchaseReturn::where('supplier_id', $supplier->id)->whereHas('purchase', fn ($q) => $q->where('payment_term', 'credit'))->get() as $return) {
            $rows[] = [
                'date' => $return->return_date,
                'particulars' => "Purchase Return #{$return->return_no}",
                'reference' => $return->return_no,
                'debit' => (float) $return->total_amount,
                'credit' => 0,
            ];
        }

        // A supplier's opening_balance is what we already owed them before
        // using this system - a payable, i.e. the credit side, so it's
        // negated going into the debit-positive running-balance convention
        // withRunningBalance() uses (balance += debit - credit).
        return $this->buildPeriodLedger($rows, -(float) $supplier->opening_balance, $fromDate, $toDate) + ['supplier' => $supplier];
    }

    public function supplierLedger(Supplier $supplier, Request $request)
    {
        return view('admin.reports.supplier-ledger', $this->supplierLedgerData($supplier, $request->from_date, $request->to_date));
    }

    public function supplierLedgerPdf(Supplier $supplier, Request $request)
    {
        $data = $this->supplierLedgerData($supplier, $request->from_date, $request->to_date);
        $pdf = Pdf::loadView('admin.exports.supplier-ledger-pdf', $data);
        return $pdf->download('supplier-ledger-' . $supplier->code . '-' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    // 12. ACCOUNT LEDGER / CASH BOOK / BANK BOOK (khata-style running balance)
    // =============================================

    /**
     * Generic per-account ledger straight off JournalEntry, the same rows
     * Account::balance already sums - so "Cash Book" and "Bank Book" are
     * just this same report pointed at account 1010/1020, not separate
     * features. Running balance shown debit-positive (see LedgerHelper) with
     * a Dr/Cr suffix in the view, so a credit-normal account (e.g. a
     * payable) still reads as a normal growing amount instead of a negative
     * number.
     */
    private function accountLedgerData(Account $account, ?string $fromDate, ?string $toDate): array
    {
        $rows = JournalEntry::where('account_id', $account->id)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->entry_date,
                'particulars' => $entry->description ?: (ucfirst(str_replace('_', ' ', $entry->reference_type)) . ' #' . $entry->reference_id),
                'reference' => ucfirst(str_replace('_', ' ', $entry->reference_type)) . ' #' . $entry->reference_id,
                'debit' => $entry->type === 'debit' ? (float) $entry->amount : 0,
                'credit' => $entry->type === 'credit' ? (float) $entry->amount : 0,
            ])
            ->toArray();

        return $this->buildPeriodLedger($rows, 0, $fromDate, $toDate) + ['account' => $account];
    }

    public function accountLedger(Account $account, Request $request)
    {
        return view('admin.reports.account-ledger', $this->accountLedgerData($account, $request->from_date, $request->to_date));
    }

    public function accountLedgerPdf(Account $account, Request $request)
    {
        $data = $this->accountLedgerData($account, $request->from_date, $request->to_date);
        $pdf = Pdf::loadView('admin.exports.account-ledger-pdf', $data);
        return $pdf->download('account-ledger-' . $account->code . '-' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    // 13. DAY BOOK (General Journal - every voucher for a date/range)
    // =============================================

    private function dayBookData(?string $fromDate, ?string $toDate): array
    {
        $fromDate = $fromDate ?: date('Y-m-d');
        $toDate = $toDate ?: $fromDate;

        $entries = JournalEntry::with('account')
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderBy('entry_date')
            ->orderBy('reference_type')
            ->orderBy('reference_id')
            ->orderBy('id')
            ->get();

        $totalDebit = (float) $entries->where('type', 'debit')->sum('amount');
        $totalCredit = (float) $entries->where('type', 'credit')->sum('amount');

        return [
            'entries' => $entries,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ];
    }

    public function dayBook(Request $request)
    {
        return view('admin.reports.day-book', $this->dayBookData($request->from_date, $request->to_date));
    }

    public function dayBookPdf(Request $request)
    {
        $data = $this->dayBookData($request->from_date, $request->to_date);
        $pdf = Pdf::loadView('admin.exports.day-book-pdf', $data);
        return $pdf->download('day-book-' . $data['from_date'] . '_to_' . $data['to_date'] . '.pdf');
    }

    // =============================================
    // Shared: opening-balance-as-of-from_date + windowed running balance,
    // used by every per-party/per-account ledger above.
    // =============================================
    private function buildPeriodLedger(array $rows, float $openingBalance, ?string $fromDate, ?string $toDate): array
    {
        usort($rows, fn ($a, $b) => strtotime($a['date']) <=> strtotime($b['date']));

        $periodRows = $rows;

        if ($fromDate) {
            foreach ($rows as $row) {
                if (strtotime($row['date']) < strtotime($fromDate)) {
                    $openingBalance += (float) $row['debit'] - (float) $row['credit'];
                }
            }
            $periodRows = array_values(array_filter($rows, fn ($r) => strtotime($r['date']) >= strtotime($fromDate)));
        }

        if ($toDate) {
            $periodRows = array_values(array_filter($periodRows, fn ($r) => strtotime($r['date']) <= strtotime($toDate . ' 23:59:59')));
        }

        $ledger = LedgerHelper::withRunningBalance($periodRows, $openingBalance);

        return [
            'rows' => $ledger['rows'],
            'opening_balance' => $ledger['opening_balance'],
            'opening_balance_side' => $ledger['opening_balance'] >= 0 ? 'Dr' : 'Cr',
            'closing_balance' => $ledger['closing_balance'],
            'closing_balance_side' => $ledger['closing_balance'] >= 0 ? 'Dr' : 'Cr',
            'total_debit' => array_sum(array_column($ledger['rows'], 'debit')),
            'total_credit' => array_sum(array_column($ledger['rows'], 'credit')),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }
}
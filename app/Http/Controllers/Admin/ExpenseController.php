<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('category', 'createdBy')
            ->orderBy('expense_date', 'desc')
            ->get();

        // Summary stats. totalExpenses matches what's actually posted to the
        // ledger (approved+paid only) - pending/cancelled never hit the
        // books, so including them here overstated this figure against the
        // Accounts page and the P&L's operating-expenses line.
        $totalExpenses = Expense::whereIn('status', ['approved', 'paid'])->sum('amount');
        $pendingExpenses = Expense::where('status', 'pending')->sum('amount');
        $approvedExpenses = Expense::where('status', 'approved')->sum('amount');
        $paidExpenses = Expense::where('status', 'paid')->sum('amount');

        return view('admin.expenses.index', compact(
            'expenses',
            'totalExpenses',
            'pendingExpenses',
            'approvedExpenses',
            'paidExpenses'
        ));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $cashBalance = Account::where('code', '1010')->first()->balance ?? 0;
        $bankBalance = Account::where('code', '1020')->first()->balance ?? 0;
        return view('admin.expenses.create', compact('categories', 'cashBalance', 'bankBalance'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'status' => 'required|in:pending,approved,paid,cancelled',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        DB::transaction(function () use ($validated) {
            $receiptPath = null;
            if (request()->hasFile('receipt')) {
                $receiptPath = request()->file('receipt')->store('expenses', 'public');
            }

            // Expense::created() posts accounting automatically when status
            // is approved/paid - no need to call it again here.
            Expense::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'status' => $validated['status'],
                'receipt' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense created successfully!');
    }

    public function show(Expense $expense)
    {
        $expense->load('category', 'createdBy', 'approvedBy');
        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $cashBalance = Account::where('code', '1010')->first()->balance ?? 0;
        $bankBalance = Account::where('code', '1020')->first()->balance ?? 0;
        return view('admin.expenses.edit', compact('expense', 'categories', 'cashBalance', 'bankBalance'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'status' => 'required|in:pending,approved,paid,cancelled',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        DB::transaction(function () use ($validated, $expense) {
            $receiptPath = $expense->receipt;
            if (request()->hasFile('receipt')) {
                if ($expense->receipt && file_exists(storage_path('app/public/' . $expense->receipt))) {
                    unlink(storage_path('app/public/' . $expense->receipt));
                }
                $receiptPath = request()->file('receipt')->store('expenses', 'public');
            }

            // Expense::updated() reverses+reposts accounting automatically
            // whenever status/amount/payment_method actually changed.
            $expense->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'status' => $validated['status'],
                'receipt' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    public function approve(Expense $expense)
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense approved successfully!');
    }

    public function markAsPaid(Expense $expense)
    {
        $expense->update([
            'status' => 'paid',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense marked as paid!');
    }

    public function cancel(Expense $expense)
    {
        $expense->update(['status' => 'cancelled']);

        return back()->with('success', 'Expense cancelled!');
    }
}

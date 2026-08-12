<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index()
    {
        $incomes = Income::with('category', 'createdBy')
            ->orderBy('income_date', 'desc')
            ->get();

        // Summary stats
        $totalIncome = Income::sum('amount');
        $monthlyIncome = Income::whereMonth('income_date', date('m'))
            ->whereYear('income_date', date('Y'))
            ->sum('amount');

        return view('admin.incomes.index', compact('incomes', 'totalIncome', 'monthlyIncome'));
    }

    public function create()
    {
        $categories = IncomeCategory::active()->orderBy('name')->get();
        return view('admin.incomes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:income_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'source' => 'required|in:sale,investment,loan,other',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        DB::transaction(function () use ($validated) {
            $receiptPath = null;
            if (request()->hasFile('receipt')) {
                $receiptPath = request()->file('receipt')->store('incomes', 'public');
            }

            Income::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'amount' => $validated['amount'],
                'income_date' => $validated['income_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'source' => $validated['source'],
                'receipt' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income recorded successfully!');
    }

    public function show(Income $income)
    {
        $income->load('category', 'createdBy');
        return view('admin.incomes.show', compact('income'));
    }

    public function edit(Income $income)
    {
        $categories = IncomeCategory::active()->orderBy('name')->get();
        return view('admin.incomes.edit', compact('income', 'categories'));
    }

    public function update(Request $request, Income $income)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:income_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'source' => 'required|in:sale,investment,loan,other',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        DB::transaction(function () use ($validated, $income) {
            $receiptPath = $income->receipt;
            if (request()->hasFile('receipt')) {
                if ($income->receipt && file_exists(storage_path('app/public/' . $income->receipt))) {
                    unlink(storage_path('app/public/' . $income->receipt));
                }
                $receiptPath = request()->file('receipt')->store('incomes', 'public');
            }

            // Income::updated() reverses+reposts accounting automatically
            // whenever amount/payment_method/source actually changed.
            $income->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'amount' => $validated['amount'],
                'income_date' => $validated['income_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'source' => $validated['source'],
                'receipt' => $receiptPath,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income updated successfully!');
    }

    public function destroy(Income $income)
    {
        $income->delete();
        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income deleted successfully!');
    }
}

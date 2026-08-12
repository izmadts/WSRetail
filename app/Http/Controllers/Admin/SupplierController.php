<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Account;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        // Load with purchases_count and balance
        $suppliers = Supplier::withCount('purchases')
            ->orderBy('name')
            ->get();
            
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:suppliers',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:suppliers',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'required|string|max:50|unique:suppliers',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'ntn' => 'nullable|string|max:20',
            'strn' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_days'] = $validated['credit_days'] ?? 0;

        $supplier = Supplier::create($validated);
        $supplier->postOpeningBalance();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'purchasePayments', 'payments' => function ($query) {
            $query->orderBy('payment_date', 'desc');
        }]);

        // Available cash/bank - used to warn (not block) if a payment would
        // take the paying account negative. See Account::getBalanceAttribute().
        $cashBalance = Account::where('code', '1010')->first()->balance ?? 0;
        $bankBalance = Account::where('code', '1020')->first()->balance ?? 0;

        return view('admin.suppliers.show', compact('supplier', 'cashBalance', 'bankBalance'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', Rule::unique('suppliers')->ignore($supplier->id)],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('suppliers')->ignore($supplier->id)],
            'phone' => 'nullable|string|max:50',
            'mobile' => ['required', 'string', 'max:50', Rule::unique('suppliers')->ignore($supplier->id)],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'ntn' => 'nullable|string|max:20',
            'strn' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $supplier->update($validated);
        $supplier->postOpeningBalance();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with purchase records!');
        }

        if ($supplier->purchasePayments()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with payment records!');
        }

        if ($supplier->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with payment records!');
        }

        $supplier->delete();
        
        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();
        
        $status = $supplier->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Supplier {$status} successfully!");
    }

    /**
     * Get supplier balance for API/AJAX
     */
    public function getBalance(Supplier $supplier)
    {
        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name,
            'opening_balance' => $supplier->opening_balance,
            'total_purchases' => $supplier->total_purchases,
            'total_paid' => $supplier->total_paid,
            'total_direct_paid' => $supplier->total_direct_paid,
            'balance' => $supplier->balance,
            'formatted_balance' => $supplier->formatted_balance,
        ]);
    }

    /**
     * Record a payment to this supplier that isn't against any specific
     * Purchase - e.g. settling opening_balance or an advance. Not capped
     * at the outstanding balance - overpaying is allowed (it just leaves
     * the supplier with a negative/"Extra" balance, i.e. an advance owed
     * back to us); the UI only soft-warns about this, doesn't block it.
     */
    public function makePayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'bank_service_charge' => 'nullable|numeric|min:0',
        ]);

        try {
            $supplier->makePayment(
                $validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['reference_no'] ?? null,
                $validated['notes'] ?? null
            );

            Expense::recordBankServiceCharge(
                $validated['bank_service_charge'] ?? 0,
                $validated['payment_date'],
                $validated['payment_method'],
                "Bank charge for payment to supplier: {$supplier->name}"
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function updatePayment(Request $request, Supplier $supplier, SupplierPayment $payment)
    {
        if ((int) $payment->supplier_id !== (int) $supplier->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $supplier->updatePayment(
                $payment,
                $validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['reference_no'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment updated successfully!');
    }

    public function deletePayment(Supplier $supplier, SupplierPayment $payment)
    {
        if ((int) $payment->supplier_id !== (int) $supplier->id) {
            abort(404);
        }

        $supplier->reversePayment($payment);

        return back()->with('success', 'Payment deleted and accounting reversed.');
    }
}
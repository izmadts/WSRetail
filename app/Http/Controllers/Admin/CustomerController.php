<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('createdByAgent', 'customerGroup')->withCount('sales')->orderBy('name')->get();
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        $agents = User::where('role', 'sales_agent')->where('is_active', true)->whereNotNull('approved_at')->orderBy('name')->get();
        $customerGroups = CustomerGroup::active()->orderBy('name')->get();
        return view('admin.customers.create', compact('agents', 'customerGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:customers',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'required|string|max:50|unique:customers',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'ntn' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'created_by_agent_id' => 'nullable|exists:users,id',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_days'] = $validated['credit_days'] ?? 0;
        if (!empty($validated['created_by_agent_id'])) {
            $validated['is_agent_customer'] = true;
        } else {
            $validated['is_agent_customer'] = false;
        }
        $customer = Customer::create($validated);
        $customer->postOpeningBalance();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'salePayments', 'payments' => function ($query) {
            $query->orderBy('payment_date', 'desc');
        }]);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $agents = User::where('role', 'sales_agent')->where('is_active', true)->whereNotNull('approved_at')->orderBy('name')->get();
        $customerGroups = CustomerGroup::active()->orderBy('name')->get();
        return view('admin.customers.edit', compact('customer', 'agents', 'customerGroups'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', Rule::unique('customers')->ignore($customer->id)],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'nullable|string|max:50',
            'mobile' => ['required', 'string', 'max:50', Rule::unique('customers')->ignore($customer->id)],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'cnic' => 'nullable|string|max:20',
            'ntn' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'created_by_agent_id' => 'nullable|exists:users,id',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        if (!empty($validated['created_by_agent_id'])) {
            $validated['is_agent_customer'] = true;
        } else {
            $validated['is_agent_customer'] = false;
        }

        $customer->update($validated);
        $customer->postOpeningBalance();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with sales records!');
        }

        if ($customer->payments()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with payment records!');
        }

        $customer->delete();
        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->is_active = !$customer->is_active;
        $customer->save();
        return back()->with('success', 'Customer status updated!');
    }

    /**
     * Record a payment received from this customer that isn't against any
     * specific Sale - e.g. settling opening_balance or an advance. Not
     * capped at the outstanding balance - overpaying is allowed (it just
     * leaves the customer with a negative/"Extra" balance, i.e. an
     * advance they can draw down later); the UI only soft-warns about
     * this, doesn't block it.
     */
    public function makePayment(Request $request, Customer $customer)
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
            $customer->makePayment(
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
                "Bank charge for payment from customer: {$customer->name}"
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function updatePayment(Request $request, Customer $customer, CustomerPayment $payment)
    {
        if ((int) $payment->customer_id !== (int) $customer->id) {
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
            $customer->updatePayment(
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

    public function deletePayment(Customer $customer, CustomerPayment $payment)
    {
        if ((int) $payment->customer_id !== (int) $customer->id) {
            abort(404);
        }

        $customer->reversePayment($payment);

        return back()->with('success', 'Payment deleted and accounting reversed.');
    }
}

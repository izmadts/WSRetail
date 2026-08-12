<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::where('created_by_agent_id', Auth::id())
            ->with('customerGroup')
            ->orderBy('name')
            ->get();
        return view('agent.customers.index', compact('customers'));
    }

    public function create()
    {
        $customerGroups = CustomerGroup::active()->orderBy('name')->get();
        return view('agent.customers.create', compact('customerGroups'));
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
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_days'] = $validated['credit_days'] ?? 0;
        $validated['created_by_agent_id'] = Auth::id();
        $validated['is_agent_customer'] = true;

        $customer = Customer::create($validated);

        return redirect()->route('agent.customers.index')
            ->with('success', 'Customer created successfully!');
    }

    public function show(Customer $customer)
    {
        // Ensure customer belongs to this agent
        if ($customer->created_by_agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $customer->load(['sales' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('agent.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        if ($customer->created_by_agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $customerGroups = CustomerGroup::active()->orderBy('name')->get();
        return view('agent.customers.edit', compact('customer', 'customerGroups'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->created_by_agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

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
            'customer_group_id' => 'nullable|exists:customer_groups,id',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $customer->update($validated);

        return redirect()->route('agent.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->created_by_agent_id != Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with sales records!');
        }

        $customer->delete();
        return redirect()->route('agent.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}
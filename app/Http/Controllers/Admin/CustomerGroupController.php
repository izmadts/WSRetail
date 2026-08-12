<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $customerGroups = CustomerGroup::withCount('customers')->orderBy('name')->get();
        return view('admin.settings.customer-groups.index', compact('customerGroups'));
    }

    public function create()
    {
        return view('admin.settings.customer-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:customer_groups',
            'price_field' => 'required|in:sale_price,wholesale_price',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['is_default']) {
            CustomerGroup::where('is_default', true)->update(['is_default' => false]);
        }

        CustomerGroup::create($validated);

        return redirect()->route('admin.settings.customer-groups.index')
            ->with('success', 'Customer group created successfully!');
    }

    public function edit(CustomerGroup $customerGroup)
    {
        return view('admin.settings.customer-groups.edit', compact('customerGroup'));
    }

    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('customer_groups')->ignore($customerGroup->id)],
            'price_field' => 'required|in:sale_price,wholesale_price',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['is_default']) {
            CustomerGroup::where('id', '!=', $customerGroup->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $customerGroup->update($validated);

        return redirect()->route('admin.settings.customer-groups.index')
            ->with('success', 'Customer group updated successfully!');
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        if ($customerGroup->customers()->count() > 0) {
            return back()->with('error', 'Cannot delete a group with customers assigned to it!');
        }

        $customerGroup->delete();

        return redirect()->route('admin.settings.customer-groups.index')
            ->with('success', 'Customer group deleted successfully!');
    }
}

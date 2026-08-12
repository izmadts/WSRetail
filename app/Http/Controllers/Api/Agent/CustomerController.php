<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Http\Resources\Api\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends ApiController
{
    public function index(Request $request)
    {
        $query = Customer::where('created_by_agent_id', $this->agent()->id)->with('customerGroup');
        $this->scopeToChannel($query);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate($request->input('per_page', 30));

        return $this->paginated($customers, fn ($c) => new CustomerResource($c));
    }

    /**
     * Defensive filter for a channel-restricted agent: only their own
     * customers whose group matches (or has no group set yet). A customer's
     * group can change after creation (e.g. an admin reassigns it), so this
     * isn't redundant with the store()/update() gate below - it's what keeps
     * the agent's own customer list honest afterward too.
     */
    private function scopeToChannel($query)
    {
        $agent = $this->agent();
        if (empty($agent->channel) || $agent->channel === 'both') {
            return;
        }

        $priceField = $agent->channel === 'wholesale' ? 'wholesale_price' : 'sale_price';
        $query->where(function ($q) use ($priceField) {
            $q->whereDoesntHave('customerGroup')
                ->orWhereHas('customerGroup', fn ($g) => $g->where('price_field', $priceField));
        });
    }

    /**
     * A channel-restricted agent may only assign a customer_group matching
     * their channel. If none was given, auto-assign the matching group
     * rather than leaving it unset - "retail-only" or "wholesale-only"
     * should mean their customers actually end up in that group, not just
     * that they were blocked from picking the wrong one.
     */
    private function resolveChannelGroup(?int $customerGroupId): array
    {
        $agent = $this->agent();
        if (empty($agent->channel) || $agent->channel === 'both') {
            return [$customerGroupId, null];
        }

        $priceField = $agent->channel === 'wholesale' ? 'wholesale_price' : 'sale_price';

        if ($customerGroupId) {
            $group = CustomerGroup::find($customerGroupId);
            if ($group && !$agent->allowsPriceField($group->price_field)) {
                return [null, "You are restricted to {$agent->channel} customers and cannot assign the \"{$group->name}\" group."];
            }
            return [$customerGroupId, null];
        }

        $defaultGroupId = CustomerGroup::where('price_field', $priceField)->value('id');
        return [$defaultGroupId, null];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();

        [$groupId, $groupError] = $this->resolveChannelGroup($validated['customer_group_id'] ?? null);
        if ($groupError) {
            return $this->error($groupError, 422, ['customer_group_id' => [$groupError]]);
        }

        $validated['customer_group_id'] = $groupId;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_days'] = $validated['credit_days'] ?? 0;
        $validated['created_by_agent_id'] = $this->agent()->id;
        $validated['is_agent_customer'] = true;

        $customer = Customer::create($validated);

        return $this->success(new CustomerResource($customer->load('customerGroup')), 'Customer created successfully.', 201);
    }

    public function show(Customer $customer)
    {
        if ($customer->created_by_agent_id != $this->agent()->id) {
            return $this->error('You do not have access to this customer.', 403);
        }

        $customer->load(['customerGroup', 'sales' => fn ($q) => $q->orderByDesc('created_at')->limit(10)]);

        return $this->success(new CustomerResource($customer));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->created_by_agent_id != $this->agent()->id) {
            return $this->error('You do not have access to this customer.', 403);
        }

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();

        if (array_key_exists('customer_group_id', $validated)) {
            [$groupId, $groupError] = $this->resolveChannelGroup($validated['customer_group_id']);
            if ($groupError) {
                return $this->error($groupError, 422, ['customer_group_id' => [$groupError]]);
            }
            $validated['customer_group_id'] = $groupId;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $customer->update($validated);

        return $this->success(new CustomerResource($customer->fresh('customerGroup')), 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->created_by_agent_id != $this->agent()->id) {
            return $this->error('You do not have access to this customer.', 403);
        }

        if ($customer->sales()->count() > 0) {
            return $this->error('Cannot delete customer with sales records.', 422);
        }

        $customer->delete();

        return $this->success(null, 'Customer deleted successfully.');
    }

    /**
     * A channel-restricted agent only sees the one group they're allowed to
     * assign - keeps the picker in the app from ever offering a choice that
     * would just 422 on submit.
     */
    public function groups()
    {
        $agent = $this->agent();
        $query = CustomerGroup::active()->orderBy('name');

        if (!empty($agent->channel) && $agent->channel !== 'both') {
            $priceField = $agent->channel === 'wholesale' ? 'wholesale_price' : 'sale_price';
            $query->where('price_field', $priceField);
        }

        return $this->success($query->get(['id', 'name', 'price_field', 'discount_percent', 'is_default']));
    }
}

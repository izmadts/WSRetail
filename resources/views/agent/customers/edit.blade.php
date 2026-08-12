@extends('layouts.agent')
@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer: ' . $customer->name)
@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Customer</h3></div>
    <div class="p-6">
        <form action="{{ route('agent.customers.update', $customer) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Group</label>
                    <select name="customer_group_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="">None</option>
                        @foreach($customerGroups as $group)
                        <option value="{{ $group->id }}" {{ old('customer_group_id', $customer->customer_group_id) == $group->id ? 'selected' : '' }}>
                            {{ $group->name }} @if($group->price_field == 'wholesale_price') (Wholesale pricing) @else (Retail pricing) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Code</label><input type="text" name="code" value="{{ old('code', $customer->code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label><input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Mobile <span class="text-red-500">*</span></label><input type="text" name="mobile" value="{{ old('mobile', $customer->mobile) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label><input type="text" name="cnic" value="{{ old('cnic', $customer->cnic) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="12345-1234567-8"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Address</label><textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('address', $customer->address) }}</textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">City</label><input type="text" name="city" value="{{ old('city', $customer->city) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">State</label><input type="text" name="state" value="{{ old('state', $customer->state) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Country</label><input type="text" name="country" value="{{ old('country', $customer->country) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label><input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance</label><input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $customer->opening_balance) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" min="0"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit</label><input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" min="0"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Credit Days</label><input type="number" name="credit_days" value="{{ old('credit_days', $customer->credit_days) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" min="0"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('notes', $customer->notes) }}</textarea></div>
                <div class="md:col-span-2"><label class="flex items-center"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }} class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-600">Active</span></label></div>
            </div>
            <div class="mt-6 flex flex-wrap items-center gap-3"><button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700"><i class="fas fa-save mr-1"></i> Update Customer</button><a href="{{ route('agent.customers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">Cancel</a></div>
        </form>
    </div>
</div>
@endsection
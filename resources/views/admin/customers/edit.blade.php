@extends('layouts.admin')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer: ' . $customer->name)

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Customer
        </h3>
    </div>

    <div class="p-4 sm:p-6">
        <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Agent Selection -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Agent</label>
                    <select name="created_by_agent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="">None</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ old('created_by_agent_id', $customer->created_by_agent_id) == $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }} ({{ $agent->email }})
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Assign to agent. Agent will see this customer in their dashboard.</p>
                </div>
                <!-- Customer Group -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Group</label>
                    <select name="customer_group_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('customer_group_id') border-red-500 @enderror">
                        <option value="">None</option>
                        @foreach($customerGroups as $group)
                        <option value="{{ $group->id }}" {{ old('customer_group_id', $customer->customer_group_id) == $group->id ? 'selected' : '' }}>
                            {{ $group->name }} @if($group->price_field == 'wholesale_price') (Wholesale pricing) @else (Retail pricing) @endif
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Determines which price (retail/wholesale) is auto-filled for this customer when creating a sale.</p>
                    @error('customer_group_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Code</label>
                    <input type="text" name="code" value="{{ old('code', $customer->code) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
                    @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Mobile -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile <span class="text-red-500">*</span></label>
                    <input type="text" name="mobile" value="{{ old('mobile', $customer->mobile) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mobile') border-red-500 @enderror">
                    @error('mobile')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- CNIC -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" value="{{ old('cnic', $customer->cnic) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cnic') border-red-500 @enderror"
                        placeholder="12345-1234567-8">
                    @error('cnic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- NTN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NTN</label>
                    <input type="text" name="ntn" value="{{ old('ntn', $customer->ntn) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ntn') border-red-500 @enderror">
                    @error('ntn')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror">{{ old('address', $customer->address) }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $customer->city) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('city') border-red-500 @enderror">
                    @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $customer->state) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('state') border-red-500 @enderror">
                    @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input type="text" name="country" value="{{ old('country', $customer->country) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('country') border-red-500 @enderror">
                    @error('country')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('postal_code') border-red-500 @enderror">
                    @error('postal_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Financial Fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Opening Balance
                        <x-help-tooltip>What this customer already owed before you started using this system. It's added straight into their outstanding balance shown everywhere else.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $customer->opening_balance) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('opening_balance') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('opening_balance')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Credit Limit
                        <x-help-tooltip>The most this customer should owe at once. If Settings &gt; Commission &amp; Bonus has "Block new credit sales that would exceed a customer's Credit Limit" turned on (it's off by default), a new credit sale that would push their balance past this number is refused. Leave at 0 for no limit.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('credit_limit') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('credit_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Credit Days
                        <x-help-tooltip>How many days one of this customer's credit sales can sit unpaid before it counts as "overdue" - overrides the global grace period in Settings &gt; Commission &amp; Bonus just for this customer. Leave at 0 to use the global default instead.</x-help-tooltip>
                    </label>
                    <input type="number" name="credit_days" value="{{ old('credit_days', $customer->credit_days) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('credit_days') border-red-500 @enderror"
                        min="0">
                    @error('credit_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes', $customer->notes) }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Active -->
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Update Customer
                </button>
                <a href="{{ route('admin.customers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'New Transfer')
@section('page-title', 'Create Money Transfer')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-exchange-alt text-blue-600 mr-2"></i> New Money Transfer
        </h3>
    </div>

    <div class="p-4 sm:p-6"
        x-data="{
            fromAccountId: '{{ old('from_account_id') }}',
            amount: {{ (float) old('amount', 0) }},
            balances: { {{ $accounts->map(fn($a) => "'{$a->id}': " . (float) $a->balance)->implode(', ') }} },
            get available() { return this.balances[this.fromAccountId] ?? null },
            get short() { return this.available !== null && (parseFloat(this.amount) || 0) > this.available },
        }">
        <form action="{{ route('admin.money-transfers.store') }}" method="POST"
            @submit="if (short && !confirm('This account only has Rs. ' + available.toFixed(2) + ' available - this transfer will take it negative. Submit anyway?')) $event.preventDefault()">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Account <span class="text-red-500">*</span></label>
                    <select name="from_account_id" x-model="fromAccountId" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ $account->code }})
                        </option>
                        @endforeach
                    </select>
                    @error('from_account_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="text-xs mt-1" x-show="available !== null" :class="short ? 'text-orange-600 font-medium' : 'text-gray-400'">
                        Available: Rs. <span x-text="(available ?? 0).toFixed(2)"></span>
                        <template x-if="short"> - insufficient, will go negative</template>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Account <span class="text-red-500">*</span></label>
                    <select name="to_account_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ $account->code }})
                        </option>
                        @endforeach
                    </select>
                    @error('to_account_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', 0) }}" x-model="amount" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           min="0.01" step="0.01">
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Date <span class="text-red-500">*</span></label>
                    <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('transfer_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Transaction ID">
                    @error('reference_no')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Create Transfer
                </button>
                <a href="{{ route('admin.money-transfers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
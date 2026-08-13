@extends('layouts.admin')

@section('title', 'Settings - Credit')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<form action="{{ route('admin.settings.credit.update') }}" method="POST" class="space-y-6 max-w-2xl">
    @csrf

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-credit-card text-blue-600 mr-2"></i> Customer Credit Policy</h4>
            <p class="mt-1 text-sm text-gray-500">Controls when a new Credit sale gets blocked for a customer who's overdue or over their credit limit. Both off by default - existing installs see no behavior change until you opt in here.</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Credit Hold Grace Period (days)
                    <x-help-tooltip>How many days a credit sale can go unpaid before it's considered "overdue" for hold purposes. A customer's own Credit Days (set on their profile) overrides this for that customer only.</x-help-tooltip>
                </label>
                <input type="number" name="credit_hold_grace_days" value="{{ old('credit_hold_grace_days', $settings['commission.credit_hold_grace_days']) }}" min="1" required
                    class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('credit_hold_grace_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center">
                <input type="checkbox" name="enforce_credit_block" value="1" {{ old('enforce_credit_block', $settings['commission.enforce_credit_block']) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700">Block new credit sales to customers overdue past the grace period</span>
            </label>

            <label class="flex items-center">
                <input type="checkbox" name="enforce_credit_limit" value="1" {{ old('enforce_credit_limit', $settings['commission.enforce_credit_limit']) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700">Block new credit sales that would exceed a customer's Credit Limit</span>
            </label>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-save mr-1"></i> Save Credit Settings
        </button>
    </div>
</form>
@endsection

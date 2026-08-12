@extends('layouts.admin')

@section('title', 'Reconciliation Details')
@section('page-title', 'Reconciliation: #' . $bankReconciliation->id)

@section('content')
<div class="space-y-6">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm font-medium text-gray-500">Account</p>
            <p class="text-lg font-bold text-gray-900">{{ $bankReconciliation->account->name ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm font-medium text-gray-500">Statement Balance</p>
            <p class="text-lg font-bold text-blue-600">{{ $bankReconciliation->formatted_statement_balance }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm font-medium text-gray-500">System Balance</p>
            <p class="text-lg font-bold text-green-600">{{ $bankReconciliation->formatted_system_balance }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm font-medium text-gray-500">Difference</p>
            <p class="text-lg font-bold {{ $bankReconciliation->difference == 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $bankReconciliation->formatted_difference }}
            </p>
        </div>
    </div>

    <!-- Reconciliation Items -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list text-gray-400 mr-2"></i> Reconciliation Items
            </h4>
            <span class="text-sm text-gray-500">{{ $bankReconciliation->items->count() }} items</span>
        </div>
        <div class="p-6">
            @if($bankReconciliation->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-left py-2 px-2">Type</th>
                                <th class="text-left py-2 px-2">Description</th>
                                <th class="text-right py-2 px-2">Amount</th>
                                <th class="text-center py-2 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($bankReconciliation->items as $item)
                            <tr>
                                <td class="py-2 px-2">{{ $item->transaction_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $item->type == 'deposit' ? 'bg-green-100 text-green-800' : 
                                           ($item->type == 'withdrawal' ? 'bg-red-100 text-red-800' : 
                                           'bg-blue-100 text-blue-800') }}">
                                        {{ $item->type_label }}
                                    </span>
                                </td>
                                <td class="py-2 px-2">{{ $item->description }}</td>
                                <td class="py-2 px-2 text-right font-medium {{ $item->type == 'deposit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $item->formatted_amount }}
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $item->status_color }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500 py-4">No items found.</p>
            @endif
        </div>
    </div>

    <!-- Actions -->
    @if($bankReconciliation->status != 'reconciled')
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-tasks text-gray-400 mr-2"></i> Actions
            </h4>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.bank-reconciliations.reconcile', $bankReconciliation) }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adjusted Balance</label>
                    <input type="number" step="0.01" name="adjusted_balance" value="{{ $bankReconciliation->statement_balance }}" required
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Reconciliation notes" value="{{ old('notes') }}"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="pt-6">
                    <button type="submit" onclick="return confirm('Reconcile this account?')"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-check mr-1"></i> Reconcile
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Back -->
    <div class="flex gap-2">
        <a href="{{ route('admin.bank-reconciliations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@endsection
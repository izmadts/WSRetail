@extends('layouts.admin')

@section('title', 'Account Details')
@section('page-title', 'Account: ' . $account->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Account Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $account->name }}</h3>
                <p class="text-sm text-gray-500">{{ $account->code }}</p>
                <div class="mt-4 flex items-center justify-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $account->type }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $account->normal_balance == 'Debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $account->normal_balance }}
                    </span>
                </div>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $account->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Parent Account</span>
                        <span class="font-medium text-gray-900">{{ $account->parent->name ?? 'None' }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                        <span class="text-gray-500 font-medium">Current Balance</span>
                        <span class="font-bold text-lg {{ $account->balance_color }}">
                            {{ $account->formatted_balance }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Created</span>
                        <span class="font-medium text-gray-900">{{ $account->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Last Updated</span>
                        <span class="font-medium text-gray-900">{{ $account->updated_at->format('d-m-Y H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex space-x-2">
                <a href="{{ route('admin.reports.account-ledger', $account) }}"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white text-center rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-book mr-1"></i> Ledger
                </a>
                <a href="{{ route('admin.accounts.edit', $account) }}"
                   class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('admin.accounts.index') }}" 
                   class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
        
        <!-- Children Accounts -->
        @if($account->children->count() > 0)
        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-sitemap text-gray-400 mr-2"></i> Sub-Accounts
                    <span class="ml-2 text-sm text-gray-500">{{ $account->children->count() }} total</span>
                </h4>
            </div>
            <div class="p-6">
                @foreach($account->children as $child)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                    <div>
                        <span class="font-medium text-gray-900">{{ $child->name }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $child->code }}</span>
                    </div>
                    <div class="text-sm font-medium {{ $child->balance_color }}">
                        {{ $child->formatted_balance }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    
    <!-- Transactions -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-gray-400 mr-2"></i> Recent Transactions
                    <span class="ml-2 text-sm text-gray-500">(Last 20 entries)</span>
                </h4>
            </div>
            <div class="p-6">
                @if($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2">Date</th>
                                    <th class="text-left py-2 px-2">Description</th>
                                    <th class="text-left py-2 px-2">Reference</th>
                                    <th class="text-right py-2 px-2">Debit</th>
                                    <th class="text-right py-2 px-2">Credit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-2">{{ $transaction->entry_date->format('d-m-Y') }}</td>
                                    <td class="py-2 px-2">{{ $transaction->description }}</td>
                                    <td class="py-2 px-2">
                                        <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">
                                            {{ ucfirst($transaction->reference_type) }}
                                            #{{ $transaction->reference_id }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 text-right font-medium text-red-600">
                                        @if($transaction->type == 'debit')
                                            Rs. {{ number_format($transaction->amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 px-2 text-right font-medium text-green-600">
                                        @if($transaction->type == 'credit')
                                            Rs. {{ number_format($transaction->amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-gray-500 py-4">No transactions found for this account.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Accounts')
@section('page-title', 'Chart of Accounts')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-book text-gray-400 mr-2"></i> All Accounts
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $accounts->count() }} total</span>
        </div>
        <a href="{{ route('admin.accounts.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> Add Account
        </a>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="accountsTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Code</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Name</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Type</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Normal Balance</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Parent</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Balance</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($accounts as $account)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $account->code }}</code>
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $account->name }}</span>
                        </td>
                        <td class="py-3 px-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $account->type == 'Asset' ? 'bg-blue-100 text-blue-800' : 
                                   ($account->type == 'Liability' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($account->type == 'Equity' ? 'bg-purple-100 text-purple-800' : 
                                   ($account->type == 'Revenue' ? 'bg-green-100 text-green-800' : 
                                   'bg-red-100 text-red-800'))) }}">
                                {{ $account->type }}
                            </span>
                        </td>
                        <td class="py-3 px-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->normal_balance == 'Debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ $account->normal_balance }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $account->parent->name ?? '-' }}
                        </td>
                        <td class="py-3 px-2 text-right font-bold {{ $account->balance_color }}">
                            {{ $account->formatted_balance }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.accounts.show', $account) }}"
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.reports.account-ledger', $account) }}"
                                   class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors duration-200" title="Ledger">
                                    <i class="fas fa-book text-sm"></i>
                                </a>
                                <a href="{{ route('admin.accounts.edit', $account) }}"
                                   class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <form action="{{ route('admin.accounts.toggle-status', $account) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 {{ $account->is_active ? 'text-gray-600 hover:bg-gray-100' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors duration-200">
                                        <i class="fas {{ $account->is_active ? 'fa-pause' : 'fa-play' }} text-sm"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.accounts.destroy', $account) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" 
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('accountsTable')) {
            new DataTable('#accountsTable', {
                pageLength: 25,
                responsive: true,
                order: [[0, 'asc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        }
    });
</script>
@endpush
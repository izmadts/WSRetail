@extends('layouts.admin')

@section('title', 'Bank Reconciliation')
@section('page-title', 'Bank Reconciliation')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-university text-gray-400 mr-2"></i> All Reconciliations
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $reconciliations->count() }} total</span>
        </div>
        <a href="{{ route('admin.bank-reconciliations.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> New Reconciliation
        </a>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="reconciliationsTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">#</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Account</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Statement</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">System</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Difference</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reconciliations as $reconciliation)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">#{{ $reconciliation->id }}</code>
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $reconciliation->account->name ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $reconciliation->statement_date->format('d-m-Y') }}
                        </td>
                        <td class="py-3 px-2 text-right font-medium">
                            Rs. {{ number_format($reconciliation->statement_balance, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right font-medium">
                            Rs. {{ number_format($reconciliation->system_balance, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right font-medium {{ $reconciliation->difference == 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rs. {{ number_format($reconciliation->difference, 2) }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $reconciliation->status_color }}">
                                {{ $reconciliation->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.bank-reconciliations.show', $reconciliation) }}" 
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                @if($reconciliation->status != 'reconciled')
                                <a href="{{ route('admin.bank-reconciliations.edit', $reconciliation) }}" 
                                   class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <form action="{{ route('admin.bank-reconciliations.destroy', $reconciliation) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" 
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                @endif
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
        if (document.getElementById('reconciliationsTable')) {
            new DataTable('#reconciliationsTable', {
                pageLength: 25,
                responsive: true,
                order: [[2, 'desc']],
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
@extends('layouts.admin')

@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
<div class="space-y-6">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($totalExpenses ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-wallet text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">Rs. {{ number_format($pendingExpenses ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Approved</p>
                    <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($approvedExpenses ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Paid</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($paidExpenses ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-double text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-sm font-medium text-gray-700">
                    <i class="fas fa-list text-gray-400 mr-2"></i> All Expenses
                </span>
                <span class="ml-2 text-sm text-gray-500">{{ $expenses->count() }} total</span>
            </div>
            <a href="{{ route('admin.expenses.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Add Expense
            </a>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="expensesTable">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">#</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Title</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Category</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Amount</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Method</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($expenses as $expense)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $expense->expense_no }}</code>
                            </td>
                            <td class="py-3 px-2">
                                <span class="font-medium text-gray-900">{{ $expense->title }}</span>
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600">
                                {{ $expense->category->name ?? '-' }}
                            </td>
                            <td class="py-3 px-2 text-right font-medium text-red-600">
                                Rs. {{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600">
                                {{ $expense->expense_date->format('d-m-Y') }}
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600">
                                {{ $expense->payment_method_label }}
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $expense->status_color }}">
                                    {{ $expense->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <a href="{{ route('admin.expenses.show', $expense) }}" 
                                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('admin.expenses.edit', $expense) }}" 
                                       class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    @if($expense->status == 'pending')
                                    <form action="{{ route('admin.expenses.approve', $expense) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Approve this expense?')" 
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-check text-sm"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if($expense->status == 'approved')
                                    <form action="{{ route('admin.expenses.mark-paid', $expense) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Mark this expense as paid?')" 
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-check-double text-sm"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @if(!in_array($expense->status, ['paid', 'cancelled']))
                                    <form action="{{ route('admin.expenses.cancel', $expense) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Cancel this expense?')" 
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-times text-sm"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this expense?')" 
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
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('expensesTable')) {
            new DataTable('#expensesTable', {
                pageLength: 25,
                responsive: true,
                order: [[4, 'desc']],
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
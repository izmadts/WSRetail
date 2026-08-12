@extends('layouts.admin')

@section('title', 'Expense Report')
@section('page-title', 'Expense Report')

@section('content')
<div class="space-y-6">
    @include('admin.partials.export-buttons', ['type' => 'expenses'])
    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category_id" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.reports.expenses') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Transactions</p>
            <p class="text-2xl font-bold">{{ $expenses->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Average Expense</p>
            <p class="text-2xl font-bold text-blue-600">
                Rs. {{ number_format($expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- By Category -->
    @if($byCategory->count() > 0)
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-chart-pie text-gray-400 mr-2"></i> Expenses by Category
            </h4>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($byCategory as $item)
                <div class="border rounded-lg p-3">
                    <p class="font-medium text-gray-900">{{ $item['category'] }}</p>
                    <p class="text-lg font-bold text-red-600">Rs. {{ number_format($item['total'], 2) }}</p>
                    <p class="text-sm text-gray-500">{{ $item['count'] }} transactions</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="expensesReportTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">#</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Title</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Category</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Amount</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Date</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td class="py-2 px-2"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $expense->expense_no }}</code></td>
                            <td class="py-2 px-2">{{ $expense->title }}</td>
                            <td class="py-2 px-2">{{ $expense->category->name ?? '-' }}</td>
                            <td class="py-2 px-2 text-right font-medium text-red-600">Rs. {{ number_format($expense->amount, 2) }}</td>
                            <td class="py-2 px-2">{{ $expense->expense_date->format('d-m-Y') }}</td>
                            <td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $expense->status_color }}">{{ $expense->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="3" class="text-right py-2 px-2">Total:</td>
                            <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($totalExpenses, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('expensesReportTable')) {
            new DataTable('#expensesReportTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [4, 'desc']
                ],
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
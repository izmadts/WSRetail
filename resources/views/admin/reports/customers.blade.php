@extends('layouts.admin')

@section('title', 'Customers Report')
@section('page-title', 'Customers Report')

@section('content')
<div class="space-y-6">
    @include('admin.partials.export-buttons', ['type' => 'customers'])
    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city" value="{{ request('city') }}" class="px-3 py-2 border border-gray-300 rounded-lg" placeholder="City">
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.reports.customers') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Customers</p>
            <p class="text-2xl font-bold">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Active Customers</p>
            <p class="text-2xl font-bold text-green-600">{{ $activeCustomers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Sales</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($totalSales, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Balance</p>
            <p class="text-2xl font-bold {{ $totalBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                Rs. {{ number_format($totalBalance, 2) }}
            </p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="customersReportTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Code</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Name</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Phone</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">City</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Sales</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Balance</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td class="py-2 px-2"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $customer->code }}</code></td>
                            <td class="py-2 px-2">{{ $customer->name }}</td>
                            <td class="py-2 px-2">{{ $customer->phone ?? '-' }}</td>
                            <td class="py-2 px-2">{{ $customer->city ?? '-' }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($customer->total_sales, 2) }}</td>
                            <td class="py-2 px-2 text-right font-medium {{ $customer->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rs. {{ number_format($customer->balance, 2) }}
                            </td>
                            <td class="py-2 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-2 px-2 text-center">
                                <a href="{{ route('admin.reports.customer-detail', $customer) }}" class="text-blue-600 hover:underline text-sm">View</a>
                                &middot;
                                <a href="{{ route('admin.reports.customer-ledger', $customer) }}" class="text-blue-600 hover:underline text-sm">Ledger</a>
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
        if (document.getElementById('customersReportTable')) {
            new DataTable('#customersReportTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [1, 'asc']
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
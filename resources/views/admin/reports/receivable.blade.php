@extends('layouts.admin')

@section('title', 'Receivable Report')
@section('page-title', 'Accounts Receivable')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2">
        @include('admin.partials.export-buttons', ['type' => 'receivable', 'showPdf' => false])
        <a href="{{ route('admin.reports.receivable-pdf', request()->all()) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-1"></i> PDF (Khata Statement)
        </a>
    </div>
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Receivable</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalReceivable, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Customers with Balance</p>
            <p class="text-2xl font-bold">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Average Receivable</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($avgReceivable, 2) }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Balance</label>
                <input type="number" name="min_balance" value="{{ request('min_balance') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Balance</label>
                <input type="number" name="max_balance" value="{{ request('max_balance') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
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
                <a href="{{ route('admin.reports.receivable') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="receivableTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Customer</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Code</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Phone</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">City</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Sales</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Paid</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Balance</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customersWithBalance as $customer)
                        <tr>
                            <td class="py-2 px-2 font-medium">{{ $customer->name }}</td>
                            <td class="py-2 px-2"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $customer->code }}</code></td>
                            <td class="py-2 px-2">{{ $customer->phone ?? '-' }}</td>
                            <td class="py-2 px-2">{{ $customer->city ?? '-' }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($customer->total_sales, 2) }}</td>
                            <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($customer->total_paid, 2) }}</td>
                            <td class="py-2 px-2 text-right font-bold text-red-600">Rs. {{ number_format($customer->balance, 2) }}</td>
                            <td class="py-2 px-2 text-center">
                                <a href="{{ route('admin.reports.customer-detail', $customer) }}" class="text-blue-600 hover:underline text-sm">View</a>
                                &middot;
                                <a href="{{ route('admin.reports.customer-ledger', $customer) }}" class="text-blue-600 hover:underline text-sm">Ledger</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="6" class="text-right py-2 px-2">Total:</td>
                            <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($totalReceivable, 2) }}</td>
                            <td></td>
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
        if (document.getElementById('receivableTable')) {
            new DataTable('#receivableTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [6, 'desc']
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
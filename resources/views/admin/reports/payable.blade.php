@extends('layouts.admin')

@section('title', 'Payable Report')
@section('page-title', 'Accounts Payable')

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('admin.reports.payable-pdf', request()->only(['city', 'min_balance'])) }}" target="_blank" class="px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">
            <i class="fas fa-file-pdf mr-1"></i> Print / PDF
        </a>
    </div>
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Payable</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalPayable, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Suppliers with Balance</p>
            <p class="text-2xl font-bold">{{ $totalSuppliers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Average Payable</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($avgPayable, 2) }}</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city" value="{{ request('city') }}" class="px-3 py-2 border border-gray-300 rounded-lg" placeholder="City">
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.reports.payable') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="payableTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Supplier</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Code</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Phone</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">City</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Purchases</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Paid</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Balance</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliersWithBalance as $supplier)
                        <tr>
                            <td class="py-2 px-2 font-medium">{{ $supplier->name }}</td>
                            <td class="py-2 px-2"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $supplier->code }}</code></td>
                            <td class="py-2 px-2">{{ $supplier->phone ?? '-' }}</td>
                            <td class="py-2 px-2">{{ $supplier->city ?? '-' }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($supplier->total_purchases, 2) }}</td>
                            <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($supplier->total_paid, 2) }}</td>
                            <td class="py-2 px-2 text-right font-bold text-red-600">Rs. {{ number_format($supplier->balance, 2) }}</td>
                            <td class="py-2 px-2 text-center">
                                <a href="{{ route('admin.reports.supplier-ledger', $supplier) }}" class="text-blue-600 hover:underline text-sm">Ledger</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="6" class="text-right py-2 px-2">Total:</td>
                            <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($totalPayable, 2) }}</td>
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
        if (document.getElementById('payableTable')) {
            new DataTable('#payableTable', {
                pageLength: 25,
                responsive: true,
                order: [[6, 'desc']],
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

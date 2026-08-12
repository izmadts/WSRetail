@extends('layouts.admin')

@section('title', 'Suppliers Report')
@section('page-title', 'Suppliers Report')

@section('content')
<div class="space-y-6">

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Suppliers</p>
            <p class="text-2xl font-bold">{{ $totalSuppliers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Active Suppliers</p>
            <p class="text-2xl font-bold text-green-600">{{ $activeSuppliers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Purchases</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($totalPurchases, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Payable</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalDue, 2) }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="suppliersReportTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Code</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Name</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Phone</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">City</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Opening Balance</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Purchases</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Paid</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Payable</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td class="py-2 px-2">
                                @if($supplier->code)
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $supplier->code }}</code>
                                @else
                                <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-2 px-2">{{ $supplier->name }}</td>
                            <td class="py-2 px-2">{{ $supplier->phone ?? '-' }}</td>
                            <td class="py-2 px-2">{{ $supplier->city ?? '-' }}</td>
                            <td class="py-2 px-2 text-right">
                                {{ $supplier->opening_balance > 0 ? 'Rs. '.number_format($supplier->opening_balance, 2) : '-' }}
                            </td>
                            <td class="py-2 px-2 text-right text-blue-600">Rs. {{ number_format($supplier->total_purchases, 2) }}</td>
                            <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($supplier->total_paid, 2) }}</td>
                            <td class="py-2 px-2 text-right font-bold {{ $supplier->total_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rs. {{ number_format($supplier->total_due, 2) }}
                            </td>
                            <td class="py-2 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-2 px-2 text-center">
                                <a href="{{ route('admin.reports.supplier-detail', $supplier) }}" class="text-blue-600 hover:underline text-sm">View</a>
                                &middot;
                                <a href="{{ route('admin.reports.supplier-ledger', $supplier) }}" class="text-blue-600 hover:underline text-sm">Ledger</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="4" class="text-right py-2 px-2">Total:</td>
                            <td class="text-right py-2 px-2">-</td>
                            <td class="text-right py-2 px-2 text-blue-600">Rs. {{ number_format($totalPurchases, 2) }}</td>
                            <td class="text-right py-2 px-2 text-green-600">Rs. {{ number_format($totalPaid, 2) }}</td>
                            <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($totalDue, 2) }}</td>
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
        if (document.getElementById('suppliersReportTable')) {
            new DataTable('#suppliersReportTable', {
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
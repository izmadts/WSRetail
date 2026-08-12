@extends('layouts.admin')

@section('title', 'Tax Report')
@section('page-title', 'Tax Report')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.reports.tax-pdf', request()->all()) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-1"></i> PDF (Khata Statement)
        </a>
    </div>
    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.reports.tax') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Sales Tax Collected</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($salesTax, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Purchase Tax Paid</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($purchaseTax, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Net Tax Payable</p>
            <p class="text-2xl font-bold {{ $netTax >= 0 ? 'text-green-600' : 'text-red-600' }}">
                Rs. {{ number_format($netTax, 2) }}
            </p>
        </div>
    </div>

    <!-- Sales with Tax -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-file-invoice text-blue-600 mr-2"></i> Sales with Tax
            </h4>
        </div>
        <div class="p-6">
            @if($sales->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-2">Invoice</th>
                            <th class="text-left py-2 px-2">Customer</th>
                            <th class="text-left py-2 px-2">Date</th>
                            <th class="text-right py-2 px-2">Total</th>
                            <th class="text-right py-2 px-2">Tax</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sales as $sale)
                        <tr>
                            <td class="py-2 px-2">{{ $sale->invoice_no }}</td>
                            <td class="py-2 px-2">{{ $sale->customer->name ?? 'N/A' }}</td>
                            <td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-blue-600">Rs. {{ number_format($sale->tax, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="4" class="text-right py-2 px-2">Total Tax:</td>
                            <td class="text-right py-2 px-2 text-blue-600">Rs. {{ number_format($salesTax, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-4">No sales with tax in this period</p>
            @endif
        </div>
    </div>

    <!-- Purchases with Tax -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-shopping-cart text-red-600 mr-2"></i> Purchases with Tax
            </h4>
        </div>
        <div class="p-6">
            @if($purchases->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-2">Invoice</th>
                            <th class="text-left py-2 px-2">Supplier</th>
                            <th class="text-left py-2 px-2">Date</th>
                            <th class="text-right py-2 px-2">Total</th>
                            <th class="text-right py-2 px-2">Tax</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($purchases as $purchase)
                        <tr>
                            <td class="py-2 px-2">{{ $purchase->invoice_no }}</td>
                            <td class="py-2 px-2">{{ $purchase->supplier->name ?? 'N/A' }}</td>
                            <td class="py-2 px-2">{{ $purchase->purchase_date->format('d-m-Y') }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-red-600">Rs. {{ number_format($purchase->tax, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="4" class="text-right py-2 px-2">Total Tax:</td>
                            <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($purchaseTax, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-4">No purchases with tax in this period</p>
            @endif
        </div>
    </div>
</div>
@endsection
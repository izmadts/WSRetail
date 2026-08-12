@extends('layouts.admin')

@section('title', 'Daily Summary')
@section('page-title', 'Daily Business Summary')

@section('content')
<div class="space-y-6">

    <!-- Date Picker -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                <input type="date" name="date" value="{{ $summary['date'] }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-calendar-day mr-1"></i> View
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.reports.daily-summary') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Today
                </a>
            </div>
        </form>
    </div>

    <!-- Date Header -->
    <div class="text-center">
        <h3 class="text-xl font-semibold text-gray-900">
            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
            {{ date('l, d F Y', strtotime($summary['date'])) }}
        </h3>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Sales</p>
                    <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($summary['total_sales'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $summary['sales_count'] }} transactions</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Purchases</p>
                    <p class="text-xl font-bold text-purple-600">Rs. {{ number_format($summary['total_purchases'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $summary['purchases_count'] }} transactions</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Expenses</p>
                    <p class="text-xl font-bold text-red-600">Rs. {{ number_format($summary['total_expenses'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $summary['expenses_count'] }} transactions</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Net Profit</p>
                    <p class="text-xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format($summary['net_profit'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500">Sales + Income - Expenses</p>
                </div>
                <div class="w-12 h-12 {{ $summary['net_profit'] >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                    <i class="fas {{ $summary['net_profit'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} {{ $summary['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-bag text-blue-600 mr-2"></i> Sales
                </h4>
            </div>
            <div class="p-4">
                @php
                    $sales = App\Models\Sale::whereDate('sale_date', $summary['date'])->whereIn('status', ['confirmed', 'partial', 'paid'])->get();
                @endphp
                @if($sales->count() > 0)
                    @foreach($sales as $sale)
                    <div class="border-b border-gray-100 py-2 last:border-0 flex justify-between">
                        <div>
                            <a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline text-sm">{{ $sale->invoice_no }}</a>
                            <p class="text-xs text-gray-500">{{ $sale->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">Rs. {{ number_format($sale->total_amount, 2) }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span>
                        </div>
                    </div>
                    @endforeach
                @else
                <p class="text-center text-gray-500 py-4">No sales for this day</p>
                @endif
            </div>
        </div>

        <!-- Purchases -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-cart text-purple-600 mr-2"></i> Purchases
                </h4>
            </div>
            <div class="p-4">
                @php
                    $purchases = App\Models\Purchase::whereDate('purchase_date', $summary['date'])->whereIn('status', ['received', 'partial', 'paid'])->get();
                @endphp
                @if($purchases->count() > 0)
                    @foreach($purchases as $purchase)
                    <div class="border-b border-gray-100 py-2 last:border-0 flex justify-between">
                        <div>
                            <a href="{{ route('admin.purchases.show', $purchase) }}" class="text-blue-600 hover:underline text-sm">{{ $purchase->invoice_no }}</a>
                            <p class="text-xs text-gray-500">{{ $purchase->supplier->name ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">Rs. {{ number_format($purchase->total_amount, 2) }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $purchase->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($purchase->status) }}</span>
                        </div>
                    </div>
                    @endforeach
                @else
                <p class="text-center text-gray-500 py-4">No purchases for this day</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
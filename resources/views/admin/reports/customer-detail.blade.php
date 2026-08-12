@extends('layouts.admin')

@section('title', 'Customer Details')
@section('page-title', 'Customer: ' . $customer->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Customer Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h3>
                <p class="text-sm text-gray-500">{{ $customer->code }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <a href="{{ route('admin.reports.customer-ledger', $customer) }}" class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    <i class="fas fa-book mr-1"></i> View Ledger (Print/PDF)
                </a>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $customer->email ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-medium">{{ $customer->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">City</span><span class="font-medium">{{ $customer->city ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Credit Limit</span><span class="font-medium">{{ $customer->formatted_credit_limit }}</span></div>
                </div>
            </div>
        </div>
        
        <!-- Financial Summary -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b"><h4 class="font-semibold">Financial Summary</h4></div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between"><span class="text-sm text-gray-500">Opening Balance</span><span class="font-medium">{{ $customer->formatted_opening_balance }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Sales</span><span class="font-medium">Rs. {{ number_format($customer->total_sales, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Paid</span><span class="font-medium text-green-600">Rs. {{ number_format($customer->total_paid, 2) }}</span></div>
                <div class="flex justify-between border-t pt-3"><span class="text-sm font-semibold">Current Balance</span><span class="font-bold {{ $customer->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $customer->formatted_balance }}</span></div>
            </div>
        </div>
        
        <a href="{{ route('admin.reports.customers') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            <i class="fas fa-arrow-left mr-1"></i> Back to Customers
        </a>
    </div>

    <!-- Sales History -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-cart text-gray-400 mr-2"></i> Sales History
                    <span class="text-sm text-gray-500 ml-2">({{ $customer->sales->count() }} transactions)</span>
                </h4>
            </div>
            <div class="p-6">
                @if($customer->sales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-2">Invoice</th>
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-right py-2 px-2">Amount</th>
                                <th class="text-right py-2 px-2">Paid</th>
                                <th class="text-right py-2 px-2">Due</th>
                                <th class="text-center py-2 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customer->sales as $sale)
                            <tr>
                                <td class="py-2 px-2"><a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline">{{ $sale->invoice_no }}</a></td>
                                <td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                                <td class="py-2 px-2 text-right text-red-600">Rs. {{ number_format($sale->due_amount, 2) }}</td>
                                <td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No sales recorded yet.</p>
                @endif
            </div>
        </div>
        
        <!-- Payments -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-credit-card text-gray-400 mr-2"></i> Payment History
                </h4>
            </div>
            <div class="p-6">
                @if($customer->salePayments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-left py-2 px-2">Invoice</th>
                                <th class="text-right py-2 px-2">Amount</th>
                                <th class="text-left py-2 px-2">Method</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customer->salePayments as $payment)
                            <tr>
                                <td class="py-2 px-2">{{ $payment->payment_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2">{{ $payment->sale->invoice_no ?? '-' }}</td>
                                <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($payment->amount, 2) }}</td>
                                <td class="py-2 px-2">{{ $payment->method_label }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No payments recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Supplier Details')
@section('page-title', 'Supplier: ' . $supplier->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Supplier Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-truck text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $supplier->name }}</h3>
                <p class="text-sm text-gray-500">{{ $supplier->code }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <a href="{{ route('admin.reports.supplier-ledger', $supplier) }}" class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    <i class="fas fa-book mr-1"></i> View Ledger (Print/PDF)
                </a>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $supplier->email ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-medium">{{ $supplier->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">City</span><span class="font-medium">{{ $supplier->city ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Credit Limit</span><span class="font-medium">{{ $supplier->formatted_credit_limit }}</span></div>
                </div>
            </div>
        </div>
        
        <!-- ✅ Financial Summary with Complete Details -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b">
                <h4 class="font-semibold text-gray-900">
                    <i class="fas fa-money-bill-wave text-gray-400 mr-2"></i> Financial Summary
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-sm text-gray-500">Opening Balance</span>
                    <span class="font-medium">
                        {{ $supplier->opening_balance > 0 ? 'Rs. '.number_format($supplier->opening_balance, 2) : '-' }}
                    </span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-sm text-gray-500">Total Purchases</span>
                    <span class="font-medium text-blue-600">Rs. {{ number_format($totalPurchases, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-sm text-gray-500">Total Paid</span>
                    <span class="font-medium text-green-600">Rs. {{ number_format($totalPaid, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-sm text-gray-500">Total Payable / Due</span>
                    <span class="font-bold {{ $totalDue > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rs. {{ number_format($totalDue, 2) }}
                    </span>
                </div>
                <div class="flex justify-between pt-2">
                    <span class="text-sm font-semibold text-gray-700">Current Balance</span>
                    <span class="font-bold {{ $balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rs. {{ number_format($balance, 2) }}
                    </span>
                </div>
                @if($supplier->credit_limit > 0)
                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="text-sm text-gray-500">Credit Limit</span>
                    <span class="font-medium">Rs. {{ number_format($supplier->credit_limit, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Available Credit</span>
                    <span class="font-medium {{ ($supplier->credit_limit - $balance) > 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format($supplier->credit_limit - $balance, 2) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        
        <a href="{{ route('admin.reports.suppliers') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Suppliers
        </a>
    </div>

    <!-- Purchase History -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-cart text-gray-400 mr-2"></i> Purchase History
                    <span class="text-sm text-gray-500 ml-2">({{ $supplier->purchases->count() }} transactions)</span>
                </h4>
            </div>
            <div class="p-6">
                @if($supplier->purchases->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-2">Invoice</th>
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-right py-2 px-2">Total</th>
                                <th class="text-right py-2 px-2">Paid</th>
                                <th class="text-right py-2 px-2">Due</th>
                                <th class="text-center py-2 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($supplier->purchases as $purchase)
                            <tr>
                                <td class="py-2 px-2"><a href="{{ route('admin.purchases.show', $purchase) }}" class="text-blue-600 hover:underline">{{ $purchase->invoice_no }}</a></td>
                                <td class="py-2 px-2">{{ $purchase->purchase_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="py-2 px-2 text-right text-red-600">Rs. {{ number_format($purchase->due_amount, 2) }}</td>
                                <td class="py-2 px-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $purchase->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($purchase->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-bold">
                            <tr>
                                <td colspan="2" class="text-right py-2 px-2">Total:</td>
                                <td class="text-right py-2 px-2 text-blue-600">Rs. {{ number_format($totalPurchases, 2) }}</td>
                                <td class="text-right py-2 px-2 text-green-600">Rs. {{ number_format($totalPaid, 2) }}</td>
                                <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($totalDue, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No purchases recorded yet.</p>
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
                @if($supplier->purchasePayments->count() > 0)
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
                            @foreach($supplier->purchasePayments as $payment)
                            <tr>
                                <td class="py-2 px-2">{{ $payment->payment_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2">{{ $payment->purchase->invoice_no ?? '-' }}</td>
                                <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($payment->amount, 2) }}</td>
                                <td class="py-2 px-2">{{ $payment->method_label }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-bold">
                            <tr>
                                <td colspan="2" class="text-right py-2 px-2">Total:</td>
                                <td class="text-right py-2 px-2 text-green-600">Rs. {{ number_format($totalPaid, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
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
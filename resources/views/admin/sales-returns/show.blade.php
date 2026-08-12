@extends('layouts.admin')

@section('title', 'Sales Return Details')
@section('page-title', 'Sales Return: ' . $salesReturn->return_no)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-gray-400 mr-2"></i> Return Info
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Return No</span>
                    <span class="font-medium">{{ $salesReturn->return_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Invoice</span>
                    <a href="{{ route('admin.sales.show', $salesReturn->sale) }}" class="text-blue-600 hover:underline">
                        {{ $salesReturn->sale->invoice_no ?? 'N/A' }}
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Customer</span>
                    <span class="font-medium">{{ $salesReturn->customer->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Return Date</span>
                    <span class="font-medium">{{ $salesReturn->return_date->format('d-m-Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Refund Method</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst(str_replace('_', ' ', $salesReturn->refund_method)) }}
                    </span>
                </div>
                @if($salesReturn->reason)
                <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Reason</p>
                    <p class="text-sm text-gray-900">{{ $salesReturn->reason }}</p>
                </div>
                @endif
                @if($salesReturn->notes)
                <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Notes</p>
                    <p class="text-sm text-gray-900">{{ $salesReturn->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-money-bill-wave text-gray-400 mr-2"></i> Financial Summary
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Sub Total</span>
                    <span class="font-medium">Rs. {{ number_format($salesReturn->sub_total, 2) }}</span>
                </div>
                @if($salesReturn->discount > 0)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Discount</span>
                    <span class="font-medium text-red-600">- Rs. {{ number_format($salesReturn->discount, 2) }}</span>
                </div>
                @endif
                @if($salesReturn->tax > 0)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Tax</span>
                    <span class="font-medium">Rs. {{ number_format($salesReturn->tax, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between border-t border-gray-200 pt-2">
                    <span class="text-sm font-bold text-gray-900">Total Return</span>
                    <span class="text-lg font-bold text-red-600">Rs. {{ number_format($salesReturn->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.sales-returns.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <form action="{{ route('admin.sales-returns.destroy', $salesReturn) }}" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Are you sure? This will reverse the return.')" 
                        class="w-full px-4 py-2 bg-red-600 text-white text-center rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-gray-400 mr-2"></i> Return Items
                </h4>
            </div>
            <div class="p-6 overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Product</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Qty</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Price</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Disc</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Tax</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($salesReturn->items as $item)
                        <tr>
                            <td class="py-2 px-2 font-medium">{{ $item->product->name ?? 'N/A' }}</td>
                            <td class="py-2 px-2 text-right">{{ number_format($item->quantity, 2) }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($item->discount, 2) }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($item->tax, 2) }}</td>
                            <td class="py-2 px-2 text-right font-medium">Rs. {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-medium">
                        <tr>
                            <td colspan="5" class="text-right py-2 px-2">Sub Total:</td>
                            <td class="text-right py-2 px-2">Rs. {{ number_format($salesReturn->sub_total, 2) }}</td>
                        </tr>
                        @if($salesReturn->discount > 0)
                        <tr>
                            <td colspan="5" class="text-right py-2 px-2">Discount:</td>
                            <td class="text-right py-2 px-2 text-red-600">- Rs. {{ number_format($salesReturn->discount, 2) }}</td>
                        </tr>
                        @endif
                        @if($salesReturn->tax > 0)
                        <tr>
                            <td colspan="5" class="text-right py-2 px-2">Tax:</td>
                            <td class="text-right py-2 px-2">Rs. {{ number_format($salesReturn->tax, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="text-lg">
                            <td colspan="5" class="text-right py-2 px-2 font-bold">Total Return:</td>
                            <td class="text-right py-2 px-2 font-bold text-red-600">Rs. {{ number_format($salesReturn->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
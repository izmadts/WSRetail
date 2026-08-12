@extends('layouts.admin')

@section('title', 'Product Details')
@section('page-title', 'Product: ' . $product->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Product Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                @if($product->image)
                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}"
                    class="w-40 h-40 object-cover rounded-2xl mx-auto mb-4 border-4 border-gray-100">
                @else
                <div class="w-40 h-40 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box text-white text-5xl"></i>
                </div>
                @endif
                <h3 class="text-xl font-bold text-gray-900">{{ $product->name }}</h3>
                <p class="text-sm text-gray-500">{{ $product->code }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($product->isLowStock())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 ml-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock
                    </span>
                    @elseif($product->isOverStock())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 ml-2">
                        <i class="fas fa-box-open mr-1"></i> Overstocked
                    </span>
                    @endif
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Category</span>
                        <span class="font-medium">{{ $product->category->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Unit</span>
                        <span class="font-medium">{{ $product->unit }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Purchase Price</span>
                        <span class="font-medium">Rs. {{ number_format($product->purchase_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Sale Price</span>
                        <span class="font-medium">Rs. {{ number_format($product->sale_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Wholesale Price</span>
                        <span class="font-medium">Rs. {{ number_format($product->wholesale_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Current Stock</span>
                        <span class="font-medium {{ $product->isLowStock() ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($product->current_stock, 2) }} {{ $product->unit }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Min Stock Level</span>
                        <span class="font-medium">{{ number_format($product->min_stock_level, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Max Stock Level</span>
                        <span class="font-medium {{ $product->isOverStock() ? 'text-amber-600' : '' }}">{{ number_format($product->max_stock_level, 2) }}</span>
                    </div>
                    @if($product->barcode)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Barcode</span>
                        <span class="font-medium">{{ $product->barcode }}</span>
                    </div>
                    @endif
                </div>
                @if($product->description)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Description</p>
                    <p class="text-sm text-gray-900">{{ $product->description }}</p>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex flex-wrap gap-2">
                <a href="{{ route('admin.products.edit', $product) }}"
                    class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('admin.products.index') }}"
                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column - Stock Movements -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Stock Movements -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900">
                    <i class="fas fa-history text-gray-400 mr-2"></i> Stock Movement History
                </h4>
            </div>
            <div class="p-6">
                @if($product->stockMovements->count() > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2">Date</th>
                            <th class="text-left py-2 px-2">Type</th>
                            <th class="text-right py-2 px-2">Quantity</th>
                            <th class="text-left py-2 px-2">Reference</th>
                            <th class="text-right py-2 px-2">Price</th>
                            <th class="text-right py-2 px-2">Stock After</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($product->stockMovements->take(10) as $movement)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-2">{{ $movement->created_at->format('d-m-Y H:i') }}</td>
                            <td class="py-2 px-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $movement->type == 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $movement->type == 'in' ? 'Stock In' : 'Stock Out' }}
                                </span>
                            </td>
                            <td class="py-2 px-2 text-right">{{ number_format($movement->quantity, 2) }}</td>
                            <td class="py-2 px-2 text-sm text-gray-600">
                                {{ ucfirst($movement->reference_type) }} #{{ $movement->reference_id }}
                            </td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($movement->unit_price, 2) }}</td>
                            <td class="py-2 px-2 text-right font-medium">{{ number_format($movement->stock_after, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($product->stockMovements->count() > 10)
                <div class="mt-3 text-center">
                    <a href="#" class="text-sm text-blue-600 hover:underline">View all movements</a>
                </div>
                @endif
                @else
                <p class="text-center text-gray-500 py-4">No stock movements recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
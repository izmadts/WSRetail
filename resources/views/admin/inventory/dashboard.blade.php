@extends('layouts.admin')

@section('title', 'Inventory Dashboard')
@section('page-title', 'Inventory Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900 truncate" title="{{ number_format($totalProducts) }}">
                        {{ \App\Helpers\NumberHelper::compact($totalProducts) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Low Stock</p>
                    <p class="text-2xl font-bold text-red-600 truncate" title="{{ number_format($lowStockCount) }}">
                        {{ \App\Helpers\NumberHelper::compact($lowStockCount) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-600 truncate" title="{{ number_format($outOfStockCount) }}">
                        {{ \App\Helpers\NumberHelper::compact($outOfStockCount) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-times-circle text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Stock Value -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Stock Value</p>
                    <p class="text-2xl font-bold text-green-600 truncate" title="Rs. {{ number_format($totalStockValue, 2) }}">
                        Rs. {{ \App\Helpers\NumberHelper::compact($totalStockValue) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <a href="{{ route('admin.inventory.adjustments.create') }}" 
           class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-edit text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">Stock Adjustment</h3>
                    <p class="text-blue-100 text-sm">Manual stock update</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('admin.inventory.history') }}" 
           class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-history text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">Stock History</h3>
                    <p class="text-purple-100 text-sm">View all movements</p>
                </div>
            </div>
        </a>
        
        <a href="{{ route('admin.products.low-stock') }}" 
           class="bg-gradient-to-r from-red-600 to-red-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">Stock Alerts</h3>
                    <p class="text-red-100 text-sm">{{ $lowStockCount }} products low</p>
                </div>
            </div>
        </a>
    </div>
    
    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-sm font-medium text-gray-700">
                    <i class="fas fa-list text-gray-400 mr-2"></i> All Products
                </span>
                <span class="ml-2 text-sm text-gray-500">{{ $products->count() }} products</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.products.index') }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-arrow-right mr-1"></i> Manage Products
                </a>
            </div>
        </div>
        
        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="inventoryProductsTable">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Code</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Product</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Category</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Unit</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Purchase Price</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Stock</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Worth</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $product->code }}</code>
                            </td>
                            <td class="py-3 px-2">
                                <span class="font-medium text-gray-900">{{ $product->name }}</span>
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600">
                                {{ $product->category->name ?? '-' }}
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600">{{ $product->unit }}</td>
                            <td class="py-3 px-2 text-right text-sm text-gray-600">
                                Rs. {{ number_format($product->purchase_price, 2) }}
                            </td>
                            <td class="py-3 px-2 text-center">
                                @if($product->isLowStock())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ number_format($product->current_stock, 2) }}
                                    </span>
                                @elseif($product->current_stock == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        {{ number_format($product->current_stock, 2) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ number_format($product->current_stock, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-right font-medium text-blue-600">
                                Rs. {{ number_format($product->worth, 2) }}
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="6" class="text-right py-3 px-2 text-gray-700">Total Stock Value:</td>
                            <td class="text-right py-3 px-2 text-blue-600 text-lg">
                                Rs. {{ number_format($totalStockValue, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Stock Movements -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-clock text-gray-400 mr-2"></i> Recent Stock Movements
            </h4>
        </div>
        <div class="p-4 sm:p-6">
            @if($recentMovements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Date</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Product</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Type</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Qty</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Reference</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Stock After</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentMovements as $movement)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="py-2 px-2 text-sm">{{ $movement->created_at->format('d-m-Y H:i') }}</td>
                                <td class="py-2 px-2 text-sm font-medium">{{ $movement->product->name ?? 'Deleted' }}</td>
                                <td class="py-2 px-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $movement->type == 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $movement->type == 'in' ? 'Stock In' : 'Stock Out' }}
                                    </span>
                                </td>
                                <td class="py-2 px-2 text-right">{{ number_format($movement->quantity, 2) }}</td>
                                <td class="py-2 px-2 text-sm text-gray-600">{{ ucfirst($movement->reference_type) }}</td>
                                <td class="py-2 px-2 text-right font-medium">{{ number_format($movement->stock_after, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No stock movements recorded yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('inventoryProductsTable')) {
            new DataTable('#inventoryProductsTable', {
                pageLength: 25,
                order: [[1, 'asc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    emptyTable: "No products found."
                }
            });
        }
    });
</script>
@endpush
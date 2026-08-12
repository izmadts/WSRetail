@extends('layouts.admin')

@section('title', 'Low Stock Products')
@section('page-title', 'Low Stock Alert')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-red-600">
        <h4 class="text-lg font-semibold text-white">
            <i class="fas fa-exclamation-triangle mr-2"></i> Products Below Minimum Stock Level
        </h4>
    </div>

    <div class="p-6">
        @if($products->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full" id="lowStockTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Code</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Name</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Category</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Current Stock</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Min Level</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Shortage</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Action</th>
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
                        <td class="py-3 px-2 text-center text-red-600 font-bold">
                            {{ number_format($product->current_stock, 2) }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            {{ number_format($product->min_stock_level, 2) }}
                        </td>
                        <td class="py-3 px-2 text-center text-red-600 font-bold">
                            {{ number_format($product->min_stock_level - $product->current_stock, 2) }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.products.edit', $product) }}"
                                    class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <i class="fas fa-plus mr-1"></i> Restock
                                </a>
                                <a href="{{ route('admin.products.show', $product) }}"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900">All Products at Safe Levels!</h3>
            <p class="text-gray-500 mt-2">All products are above their minimum stock levels.</p>
            <a href="{{ route('admin.products.index') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> Back to Products
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('lowStockTable')) {
            new DataTable('#lowStockTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [5, 'desc']
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
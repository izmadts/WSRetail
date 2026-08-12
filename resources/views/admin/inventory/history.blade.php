@extends('layouts.admin')

@section('title', 'Stock History')
@section('page-title', 'Stock Movement History')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-history text-gray-400 mr-2"></i> All Stock Movements
        </h4>
    </div>
    
    <div class="p-4 sm:p-6">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Product</label>
                    <select name="product_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.inventory.history') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
        
        <!-- Results -->
        <div class="overflow-x-auto">
            <table class="w-full" id="historyTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Product</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Type</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Quantity</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Price</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Total</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Reference</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Stock After</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($movements as $movement)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $movement->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $movement->product->name ?? 'Deleted' }}</span>
                            <span class="text-xs text-gray-500 block">{{ $movement->product->code ?? '' }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movement->type == 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $movement->type == 'in' ? 'Stock In' : 'Stock Out' }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-right font-medium">
                            {{ number_format($movement->quantity, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right text-sm text-gray-600">
                            Rs. {{ number_format($movement->unit_price, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right text-sm text-gray-600">
                            Rs. {{ number_format($movement->total_price, 2) }}
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                {{ ucfirst($movement->reference_type) }}
                            </span>
                            @if($movement->reference_type != 'adjustment')
                                <span class="text-xs">#{{ $movement->reference_id }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-right font-medium">
                            {{ number_format($movement->stock_after, 2) }}
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ Str::limit($movement->notes ?? '-', 30) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('historyTable')) {
            new DataTable('#historyTable', {
                pageLength: 50,
                responsive: true,
                order: [[0, 'desc']],
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
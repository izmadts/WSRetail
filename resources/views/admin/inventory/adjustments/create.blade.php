@extends('layouts.admin')

@section('title', 'New Stock Adjustment')
@section('page-title', 'Create Stock Adjustment')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-sliders-h text-blue-600 mr-2"></i> New Stock Adjustment
        </h3>
    </div>
    
    <div class="p-4 sm:p-6">
        <form action="{{ route('admin.inventory.adjustments.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
                    <select name="product_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('product_id') border-red-500 @enderror">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->current_stock }}">
                            {{ $product->name }} ({{ $product->code }}) - Stock: {{ number_format($product->current_stock, 2) }} {{ $product->unit }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                
                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select name="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror">
                        <option value="">Select Type</option>
                        <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>Stock In (Increase)</option>
                        <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Stock Out (Decrease)</option>
                    </select>
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                
                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="quantity" value="{{ old('quantity', 1) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('quantity') border-red-500 @enderror"
                           min="0.01" step="0.01">
                    @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                
                <!-- Current Stock Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                    <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700" id="currentStockDisplay">
                        Select a product
                    </div>
                </div>
                
                <!-- Reason -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <input type="text" name="reason" value="{{ old('reason') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reason') border-red-500 @enderror"
                           placeholder="e.g., Physical count adjustment, Damaged goods, etc.">
                    @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                
                <!-- Notes -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Create Adjustment
                </button>
                <a href="{{ route('admin.inventory.adjustments.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.querySelector('select[name="product_id"]');
        const currentStockDisplay = document.getElementById('currentStockDisplay');
        
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const stock = selectedOption.dataset.stock || 0;
                const productName = selectedOption.text.split(' - ')[0];
                currentStockDisplay.textContent = productName + ' - Current Stock: ' + parseFloat(stock).toFixed(2);
                currentStockDisplay.className = 'w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700';
            } else {
                currentStockDisplay.textContent = 'Select a product';
                currentStockDisplay.className = 'w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-400';
            }
        });
    });
</script>
@endpush
@endsection
@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product: ' . $product->name)

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Product
        </h3>
    </div>

    <div class="p-4 sm:p-6">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $product->code) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
                    @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category_id') border-red-500 @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                    <select name="unit" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('unit') border-red-500 @enderror">
                        <option value="">Select Unit</option>
                        <option value="kg" {{ old('unit', $product->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="gram" {{ old('unit', $product->unit) == 'gram' ? 'selected' : '' }}>Gram (g)</option>
                        <option value="liter" {{ old('unit', $product->unit) == 'liter' ? 'selected' : '' }}>Liter (L)</option>
                        <option value="ml" {{ old('unit', $product->unit) == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                        <option value="piece" {{ old('unit', $product->unit) == 'piece' ? 'selected' : '' }}>Piece (pc)</option>
                        <option value="box" {{ old('unit', $product->unit) == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="packet" {{ old('unit', $product->unit) == 'packet' ? 'selected' : '' }}>Packet</option>
                        <option value="bundle" {{ old('unit', $product->unit) == 'bundle' ? 'selected' : '' }}>Bundle</option>
                    </select>
                    @error('unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Purchase Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Purchase Price <span class="text-red-500">*</span>
                        <x-help-tooltip>Your cost to acquire one unit. Selling below this on a Sale shows a "below cost" warning there, so keep this current when your buying price changes.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('purchase_price') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('purchase_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Sale Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sale Price <span class="text-red-500">*</span>
                        <x-help-tooltip>The retail price charged to customers whose group prices off "Retail" (see Available For below). Only applies if "Retail" is checked.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('sale_price') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('sale_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Wholesale Price -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Wholesale Price
                        <x-help-tooltip>Charged to customers whose group prices off "Wholesale" instead of Retail. Leave blank to just use your Sale Price for wholesale customers too.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wholesale_price') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('wholesale_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Current Stock (Readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                    <input type="number" step="0.01" name="current_stock" value="{{ old('current_stock', $product->current_stock) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                        readonly>
                    <p class="text-xs text-gray-400 mt-1">Stock can only be changed via purchases/sales</p>
                </div>

                <!-- Min Stock Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Min Stock Level
                        <x-help-tooltip>Once Current Stock falls to or below this, the product is flagged as low stock wherever that's shown (e.g. a low-stock list/report) - it doesn't block any sale by itself.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('min_stock_level') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('min_stock_level')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Max Stock Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Max Stock Level
                        <x-help-tooltip>Your ceiling for how much of this to keep on hand. Once Current Stock goes above it, the product shows an "Overstocked" flag on this list and its detail page - a visual warning only, it doesn't block a purchase or stock adjustment from pushing stock higher. Leave at 0 for no ceiling.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="max_stock_level" value="{{ old('max_stock_level', $product->max_stock_level) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('max_stock_level') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('max_stock_level')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Barcode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('barcode') border-red-500 @enderror">
                    @error('barcode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    @if($product->image)
                    <div class="mb-2">
                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="h-20 object-cover rounded-lg">
                    </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('image') border-red-500 @enderror">
                    @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Available For (Retail / Wholesale) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Available For <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_retail" value="1" {{ old('is_retail', $product->is_retail) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Retail</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_wholesale" value="1" {{ old('is_wholesale', $product->is_wholesale) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Wholesale</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Controls whether this product shows up for retail customers, wholesale customers, or both when creating a sale.</p>
                    @error('is_retail')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Active -->
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Update Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
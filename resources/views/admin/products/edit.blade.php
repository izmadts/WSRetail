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
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" x-data="productVariantEditor()" x-init="init()">
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
                <div x-show="!hasVariants">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Purchase Price <span class="text-red-500">*</span>
                        <x-help-tooltip>Your cost to acquire one unit. Selling below this on a Sale shows a "below cost" warning there, so keep this current when your buying price changes.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" :required="!hasVariants"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('purchase_price') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('purchase_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Sale Price -->
                <div x-show="!hasVariants">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sale Price <span class="text-red-500">*</span>
                        <x-help-tooltip>The retail price charged to customers whose group prices off "Retail" (see Available For below). Only applies if "Retail" is checked.</x-help-tooltip>
                    </label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" :required="!hasVariants"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('sale_price') border-red-500 @enderror"
                        min="0" step="0.01">
                    @error('sale_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Wholesale Price -->
                <div x-show="!hasVariants">
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
                <div x-show="!hasVariants">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                    <input type="number" step="0.01" name="current_stock" value="{{ old('current_stock', $product->current_stock) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                        readonly>
                    <p class="text-xs text-gray-400 mt-1">Stock can only be changed via purchases/sales</p>
                </div>

                <!-- Min Stock Level -->
                <div x-show="!hasVariants">
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
                <div x-show="!hasVariants">
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
                <div x-show="!hasVariants">
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

                <!-- Variants -->
                <div class="md:col-span-2 border-t border-gray-200 pt-4">
                    <label class="flex items-center gap-2 mb-1" :class="alreadyHasVariants ? 'cursor-not-allowed opacity-70' : 'cursor-pointer'">
                        <input type="checkbox" name="has_variants" value="1" x-model="hasVariants" :disabled="alreadyHasVariants"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="font-medium text-gray-800">This product has variants (Size, Color, Storage...)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-3" x-show="alreadyHasVariants">Already enabled for this product and can't be turned off here.</p>

                    <div x-show="hasVariants" x-cloak class="bg-gray-50 rounded-xl p-4 space-y-4">

                        <!-- Existing variants -->
                        <div x-show="existingVariants.length > 0">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Existing variants</p>
                            <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-100 text-gray-500">
                                        <tr>
                                            <th class="p-2 text-left">Variant</th>
                                            <th class="p-2 text-left">SKU</th>
                                            <th class="p-2 text-left">Barcode</th>
                                            <th class="p-2 text-left">Purchase</th>
                                            <th class="p-2 text-left">Sale</th>
                                            <th class="p-2 text-left">Wholesale</th>
                                            <th class="p-2 text-left">Stock</th>
                                            <th class="p-2 text-left">Active</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="v in existingVariants" :key="v.id">
                                            <tr>
                                                <td class="p-2 font-medium text-gray-800" x-text="v.label"></td>
                                                <td class="p-1"><input type="text" x-model="v.sku" class="w-32 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="text" x-model="v.barcode" class="w-24 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.purchase_price" step="0.01" class="w-20 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.sale_price" step="0.01" class="w-20 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.wholesale_price" step="0.01" class="w-20 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-2 text-gray-500" x-text="v.current_stock"></td>
                                                <td class="p-1 text-center"><input type="checkbox" x-model="v.is_active"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Stock only changes through sales/purchases, same as the base product.</p>
                        </div>

                        <!-- Add more variants -->
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2" x-text="existingVariants.length ? 'Add more variants' : '1. Pick attributes & values for this product'"></p>

                            <template x-for="(attr, aIdx) in selectedAttributes" :key="attr.id">
                                <div class="bg-white rounded-lg p-3 mb-2 border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-sm text-gray-800" x-text="attr.name"></span>
                                        <button type="button" @click="removeAttribute(aIdx)" class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-times"></i> Remove</button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        <template x-for="val in attr.availableValues" :key="val.id">
                                            <label class="px-2.5 py-1 rounded-lg text-xs cursor-pointer border select-none"
                                                   :class="attr.selectedValueIds.includes(val.id) ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'">
                                                <input type="checkbox" class="hidden" :value="val.id" x-model="attr.selectedValueIds">
                                                <span x-text="val.value"></span>
                                            </label>
                                        </template>
                                        <input type="text" x-model="attr.newValueInput" @keydown.enter.prevent="addValueToAttribute(aIdx)"
                                               placeholder="+ new value" class="text-xs px-2 py-1 border border-gray-300 rounded-lg w-24">
                                        <button type="button" @click="addValueToAttribute(aIdx)" class="text-xs px-2 py-1 bg-gray-200 rounded-lg hover:bg-gray-300">Add</button>
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <select x-model="attributeToAdd" class="text-sm border border-gray-300 rounded-lg px-2 py-1.5">
                                    <option value="">+ Add existing attribute...</option>
                                    <template x-for="attr in availableAttributesToAdd()" :key="attr.id">
                                        <option :value="attr.id" x-text="attr.name"></option>
                                    </template>
                                </select>
                                <button type="button" @click="addExistingAttribute()" class="text-sm px-3 py-1.5 bg-gray-200 rounded-lg hover:bg-gray-300">Add</button>
                                <span class="text-gray-400 text-xs">or create a new one:</span>
                                <input type="text" x-model="newAttributeName" @keydown.enter.prevent="addNewAttribute()" placeholder="e.g. Flavor" class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 w-32">
                                <button type="button" @click="addNewAttribute()" class="text-sm px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Create</button>
                            </div>

                            <div class="mt-3" x-show="selectedAttributes.length > 0">
                                <button type="button" @click="generateVariants()" :disabled="!canGenerate()"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                    <i class="fas fa-magic mr-1"></i> Generate Variants (<span x-text="comboCount()"></span>)
                                </button>
                            </div>
                        </div>

                        <!-- New variant matrix -->
                        <div x-show="newVariants.length > 0" class="space-y-2">
                            <p class="text-sm font-semibold text-gray-700">Set price &amp; stock for the new variants</p>

                            <div class="flex flex-wrap gap-2 items-center bg-white p-2.5 rounded-lg border border-gray-200 text-xs">
                                <span class="text-gray-500 font-medium">Bulk set:</span>
                                <span>Purchase</span>
                                <input type="number" x-model.number="bulk.purchase_price" step="0.01" class="w-20 px-2 py-1 border border-gray-300 rounded">
                                <button type="button" @click="bulkApply('purchase_price')" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Apply</button>
                                <span>Sale</span>
                                <input type="number" x-model.number="bulk.sale_price" step="0.01" class="w-20 px-2 py-1 border border-gray-300 rounded">
                                <button type="button" @click="bulkApply('sale_price')" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Apply</button>
                                <span>Stock</span>
                                <input type="number" x-model.number="bulk.stock" step="0.01" class="w-20 px-2 py-1 border border-gray-300 rounded">
                                <button type="button" @click="bulkApply('stock')" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Apply</button>
                            </div>

                            <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-100 text-gray-500">
                                        <tr>
                                            <th class="p-2 text-left">Variant</th>
                                            <th class="p-2 text-left">SKU</th>
                                            <th class="p-2 text-left">Barcode</th>
                                            <th class="p-2 text-left">Purchase</th>
                                            <th class="p-2 text-left">Sale</th>
                                            <th class="p-2 text-left">Wholesale</th>
                                            <th class="p-2 text-left">Stock</th>
                                            <th class="p-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(v, i) in newVariants" :key="i">
                                            <tr>
                                                <td class="p-2 font-medium text-gray-800" x-text="v.label"></td>
                                                <td class="p-1"><input type="text" x-model="v.sku" class="w-32 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="text" x-model="v.barcode" class="w-24 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.purchase_price" step="0.01" class="w-20 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.sale_price" step="0.01" class="w-20 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.wholesale_price" step="0.01" class="w-20 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1"><input type="number" x-model.number="v.stock" step="0.01" class="w-16 px-1.5 py-1 border border-gray-300 rounded"></td>
                                                <td class="p-1 text-center"><button type="button" @click="removeNewVariant(i)" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <input type="hidden" name="existing_variants_json" :value="JSON.stringify(existingVariants)">
                        <input type="hidden" name="new_variants_json" :value="JSON.stringify(newVariants)">
                        @error('new_variants_json')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
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

@php
    $existingVariantsForJs = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'label' => $v->label,
        'sku' => $v->sku,
        'barcode' => $v->barcode,
        'purchase_price' => (float) $v->purchase_price,
        'sale_price' => (float) $v->sale_price,
        'wholesale_price' => (float) $v->wholesale_price,
        'current_stock' => (float) $v->current_stock,
        'is_active' => (bool) $v->is_active,
    ]);
@endphp

@section('scripts')
<script>
function productVariantEditor() {
    return {
        alreadyHasVariants: {{ $product->has_variants ? 'true' : 'false' }},
        hasVariants: {{ $product->has_variants ? 'true' : 'false' }},
        existingVariants: @json($existingVariantsForJs),
        allAttributes: [],
        selectedAttributes: [],
        attributeToAdd: '',
        newAttributeName: '',
        newVariants: [],
        bulk: { purchase_price: null, sale_price: null, stock: null },

        async init() {
            try {
                const res = await fetch('{{ route('admin.product-attributes.data') }}', { headers: { Accept: 'application/json' } });
                this.allAttributes = await res.json();
            } catch (e) {
                this.allAttributes = [];
            }
        },

        csrfHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            };
        },

        availableAttributesToAdd() {
            const usedIds = this.selectedAttributes.map(a => a.id);
            return this.allAttributes.filter(a => !usedIds.includes(a.id));
        },

        addExistingAttribute() {
            if (!this.attributeToAdd) return;
            const attr = this.allAttributes.find(a => a.id == this.attributeToAdd);
            if (!attr) return;
            this.selectedAttributes.push({
                id: attr.id, name: attr.name,
                availableValues: [...attr.values], selectedValueIds: [], newValueInput: '',
            });
            this.attributeToAdd = '';
        },

        async addNewAttribute() {
            const name = this.newAttributeName.trim();
            if (!name) return;
            const res = await fetch('{{ route('admin.product-attributes.store') }}', {
                method: 'POST', headers: this.csrfHeaders(), body: JSON.stringify({ name }),
            });
            if (!res.ok) { alert('Could not add that attribute - it may already exist.'); return; }
            const attr = await res.json();
            attr.values = [];
            this.allAttributes.push(attr);
            this.selectedAttributes.push({ id: attr.id, name: attr.name, availableValues: [], selectedValueIds: [], newValueInput: '' });
            this.newAttributeName = '';
        },

        removeAttribute(idx) {
            this.selectedAttributes.splice(idx, 1);
        },

        async addValueToAttribute(aIdx) {
            const attr = this.selectedAttributes[aIdx];
            const value = attr.newValueInput.trim();
            if (!value) return;
            const res = await fetch(`{{ url('admin/product-attributes') }}/${attr.id}/values`, {
                method: 'POST', headers: this.csrfHeaders(), body: JSON.stringify({ value }),
            });
            if (!res.ok) { alert('Could not add that value - it may already exist for this attribute.'); return; }
            const val = await res.json();
            attr.availableValues.push(val);
            attr.selectedValueIds.push(val.id);
            attr.newValueInput = '';
        },

        canGenerate() {
            return this.selectedAttributes.length > 0 && this.selectedAttributes.every(a => a.selectedValueIds.length > 0);
        },

        comboCount() {
            if (!this.canGenerate()) return 0;
            return this.selectedAttributes.reduce((acc, a) => acc * a.selectedValueIds.length, 1);
        },

        slug(text) {
            return (text || '').toString().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        },

        generateVariants() {
            if (!this.canGenerate()) return;

            let combos = [[]];
            for (const attr of this.selectedAttributes) {
                const selected = attr.availableValues.filter(v => attr.selectedValueIds.includes(v.id));
                const next = [];
                for (const combo of combos) {
                    for (const val of selected) {
                        next.push([...combo, { id: val.id, value: val.value }]);
                    }
                }
                combos = next;
            }

            const nameSlug = this.slug('{{ $product->name }}') || 'variant';

            this.newVariants = combos.map(combo => ({
                attribute_value_ids: combo.map(c => c.id),
                label: combo.map(c => c.value).join(' / '),
                sku: `${nameSlug}-${combo.map(c => this.slug(c.value)).join('-')}`,
                barcode: '',
                purchase_price: 0,
                sale_price: 0,
                wholesale_price: 0,
                stock: 0,
                min_stock_level: 0,
                max_stock_level: 0,
            }));
        },

        removeNewVariant(i) {
            this.newVariants.splice(i, 1);
        },

        bulkApply(field) {
            const value = this.bulk[field];
            if (value === null || value === '') return;
            this.newVariants.forEach(v => {
                if (field === 'stock') v.stock = value;
                else v[field] = value;
            });
        },
    };
}
</script>
@endsection
@csrf
@if(isset($customerGroup))
@method('PUT')
@endif
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $customerGroup->name ?? '') }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Price Basis <span class="text-red-500">*</span></label>
        <select name="price_field" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="sale_price" {{ old('price_field', $customerGroup->price_field ?? 'sale_price') == 'sale_price' ? 'selected' : '' }}>Retail (Sale) Price</option>
            <option value="wholesale_price" {{ old('price_field', $customerGroup->price_field ?? '') == 'wholesale_price' ? 'selected' : '' }}>Wholesale Price</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">Which product price column sales to customers in this group default to.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Additional Discount (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="discount_percent" value="{{ old('discount_percent', $customerGroup->discount_percent ?? 0) }}"
            class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('discount_percent')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-6">
        <label class="flex items-center">
            <input type="checkbox" name="is_default" value="1" {{ old('is_default', $customerGroup->is_default ?? false) ? 'checked' : '' }}
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">Default group for new customers</span>
        </label>
        <label class="flex items-center">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customerGroup->is_active ?? true) ? 'checked' : '' }}
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">Active</span>
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-save mr-1"></i> Save
        </button>
        <a href="{{ route('admin.settings.customer-groups.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
            Cancel
        </a>
    </div>
</div>

@csrf
@if(isset($location))
@method('PUT')
@endif
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $location->name ?? '') }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
        <input type="text" name="address" value="{{ old('address', $location->address ?? '') }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $location->phone ?? '') }}"
            class="w-full sm:w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">POS Use <span class="text-red-500">*</span></label>
        <select name="pos_type" required class="w-full sm:w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="retail" {{ old('pos_type', $location->pos_type ?? 'both') == 'retail' ? 'selected' : '' }}>Retail only</option>
            <option value="wholesale" {{ old('pos_type', $location->pos_type ?? '') == 'wholesale' ? 'selected' : '' }}>Wholesale only</option>
            <option value="both" {{ old('pos_type', $location->pos_type ?? 'both') == 'both' ? 'selected' : '' }}>Both</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">Controls what a sale created at this location can use: Retail only forces retail-priced/retail-flagged products, Wholesale only forces wholesale-priced/wholesale-flagged products, Both leaves it up to the customer's group as before.</p>
    </div>

    <div class="flex gap-6">
        <label class="flex items-center">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $location->is_active ?? true) ? 'checked' : '' }}
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">Active</span>
        </label>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-save mr-1"></i> Save
        </button>
        <a href="{{ route('admin.settings.locations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
            Cancel
        </a>
    </div>
</div>

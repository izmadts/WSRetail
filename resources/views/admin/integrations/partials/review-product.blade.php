<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-sm">
    <div class="space-y-2">
        <p class="text-xs font-semibold text-gray-500 uppercase">Product Fields</p>
        <dl class="space-y-1.5">
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Category</dt><dd class="text-gray-800">{{ $m['category_name'] ?? 'Imported (default)' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Unit</dt><dd class="text-gray-800">{{ $m['unit'] }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Brand</dt><dd class="text-gray-800">{{ $m['brand'] ?? '—' }}</dd></div>
            @if (empty($m['has_variants']))
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Sale Price</dt><dd class="text-gray-800">Rs. {{ number_format($m['sale_price'], 2) }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Wholesale Price</dt><dd class="text-gray-800">Rs. {{ number_format($m['wholesale_price'], 2) }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Stock</dt><dd class="text-gray-800">{{ $m['current_stock'] }}</dd></div>
            @endif
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Weight</dt><dd class="text-gray-800">{{ $m['weight'] ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Dimensions</dt><dd class="text-gray-800">{{ $m['length'] ?? '—' }} &times; {{ $m['width'] ?? '—' }} &times; {{ $m['height'] ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Active</dt><dd class="text-gray-800">{{ $m['is_active'] ? 'Yes' : 'No' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0">Old URL</dt><dd class="text-gray-800 font-mono text-xs break-all">{{ $m['legacy_permalink'] ?? ($m['legacy_slug'] ? '/product/' . $m['legacy_slug'] : '—') }}</dd></div>
        </dl>

        @if (!empty($m['description']))
        <div class="pt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Description</p>
            <p class="text-gray-700 text-xs whitespace-pre-line">{{ $m['description'] }}</p>
        </div>
        @endif
        @if (!empty($m['short_description']))
        <div class="pt-2">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Short Description</p>
            <p class="text-gray-700 text-xs whitespace-pre-line">{{ $m['short_description'] }}</p>
        </div>
        @endif

        @if (!empty($m['non_variation_attributes']))
        <div class="pt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-xs font-semibold text-amber-800 mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Not imported</p>
            <p class="text-xs text-amber-800 mb-1">
                These are descriptive attributes (not used to generate variations on WooCommerce) - WSRetail doesn't
                have a free-form product-specs field yet, so they're shown here for your awareness only:
            </p>
            <ul class="text-xs text-amber-900 list-disc list-inside">
                @foreach ($m['non_variation_attributes'] as $attr)
                <li>{{ $attr['name'] }}: {{ implode(', ', $attr['values']) }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="space-y-3">
        @if (!empty($m['images']))
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Images ({{ count($m['images']) }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($m['images'] as $img)
                <img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" class="w-14 h-14 rounded-lg object-cover border border-gray-200">
                @endforeach
            </div>
        </div>
        @endif

        @if (!empty($m['has_variants']))
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Variants ({{ count($m['variants']) }})</p>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-xs">
                    <thead class="bg-white text-gray-500">
                        <tr>
                            <th class="px-2 py-1.5 text-left"></th>
                            <th class="px-2 py-1.5 text-left">Variant</th>
                            <th class="px-2 py-1.5 text-left">SKU</th>
                            <th class="px-2 py-1.5 text-right">Price</th>
                            <th class="px-2 py-1.5 text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($m['variants'] as $variant)
                        <tr>
                            <td class="px-2 py-1.5">
                                @if (!empty($variant['image']))
                                    <img src="{{ $variant['image'] }}" alt="" class="w-8 h-8 rounded object-cover border border-gray-200">
                                @endif
                            </td>
                            <td class="px-2 py-1.5 text-gray-800">{{ $variant['label'] ?: '—' }}</td>
                            <td class="px-2 py-1.5 font-mono text-gray-500">{{ $variant['sku'] }}</td>
                            <td class="px-2 py-1.5 text-right text-gray-800">Rs. {{ number_format($variant['sale_price'], 2) }}</td>
                            <td class="px-2 py-1.5 text-right text-gray-800">{{ $variant['current_stock'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

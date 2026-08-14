@if ($m['kind'] === 'store_info')
    <div class="text-sm max-w-md">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Will update Settings &gt; Ecommerce &gt; Store</p>
        <dl class="space-y-1.5">
            @foreach ($m['fields'] as $key => $value)
            <div class="flex gap-2"><dt class="text-gray-400 w-32 shrink-0 capitalize">{{ str_replace('_', ' ', $key) }}</dt><dd class="text-gray-800">{{ $value }}</dd></div>
            @endforeach
        </dl>
        <p class="text-xs text-gray-400 mt-2">WooCommerce has no store phone/support-email field, so those aren't touched here - fill them in on the Store settings page directly.</p>
    </div>
@elseif ($m['kind'] === 'payment_gateway')
    <div class="text-sm max-w-md">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Will create/update in Settings &gt; Ecommerce &gt; Payment</p>
        <dl class="space-y-1.5">
            <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Code</dt><dd class="text-gray-800 font-mono text-xs">{{ $m['code'] }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Name</dt><dd class="text-gray-800">{{ $m['name'] }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Enabled</dt><dd class="text-gray-800">{{ $m['is_enabled'] ? 'Yes' : 'No' }}</dd></div>
            @if (!empty($m['description']))
            <div class="flex gap-2"><dt class="text-gray-400 w-24 shrink-0">Description</dt><dd class="text-gray-800">{{ $m['description'] }}</dd></div>
            @endif
        </dl>
    </div>
@else
    <div class="text-sm max-w-md">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Will update Settings &gt; Ecommerce &gt; Shipping</p>
        <dl class="space-y-1.5">
            <div class="flex gap-2"><dt class="text-gray-400 w-36 shrink-0">Flat Rate</dt><dd class="text-gray-800">Rs. {{ number_format($m['shipping_flat_rate'], 2) }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-36 shrink-0">Free Shipping Above</dt><dd class="text-gray-800">{{ $m['shipping_free_threshold'] ? 'Rs. ' . number_format($m['shipping_free_threshold'], 2) : 'Not set on old store' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-36 shrink-0">Source Zone</dt><dd class="text-gray-800">{{ $m['source_zone_name'] ?? '—' }}</dd></div>
        </dl>
        <p class="text-xs text-gray-400 mt-2">
            WooCommerce supports per-zone shipping rates; WSRetail supports one flat rate + one free-shipping
            threshold store-wide. This is the first enabled flat-rate/free-shipping method found across your
            zones - review it before confirming, it may not represent every zone.
        </p>
    </div>
@endif

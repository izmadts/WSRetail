<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-sm">
    <div class="space-y-2">
        <p class="text-xs font-semibold text-gray-500 uppercase">Order Info</p>
        <dl class="space-y-1.5">
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Sale Date</dt><dd class="text-gray-800">{{ $m['sale_date'] }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Payment Method</dt><dd class="text-gray-800">{{ $m['payment_method'] ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Coupon</dt><dd class="text-gray-800">{{ $m['coupon_code'] ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Sub Total</dt><dd class="text-gray-800">Rs. {{ number_format($m['sub_total'], 2) }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Shipping</dt><dd class="text-gray-800">Rs. {{ number_format($m['shipping_cost'], 2) }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Tax</dt><dd class="text-gray-800">Rs. {{ number_format($m['tax'], 2) }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Total</dt><dd class="text-gray-800 font-semibold">Rs. {{ number_format($m['total_amount'], 2) }}</dd></div>
        </dl>

        <p class="text-xs font-semibold text-gray-500 uppercase pt-2">Customer</p>
        <dl class="space-y-1.5">
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Name</dt><dd class="text-gray-800">{{ $m['customer']['name'] }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Email</dt><dd class="text-gray-800">{{ $m['customer']['email'] ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="text-gray-400 w-28 shrink-0">Phone</dt><dd class="text-gray-800">{{ $m['customer']['phone'] ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="space-y-3">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Billing Address</p>
            @if (!empty($m['billing_address']))
            <dl class="text-xs space-y-1">
                @foreach ($m['billing_address'] as $key => $value)
                <div class="flex gap-2"><dt class="text-gray-400 w-20 shrink-0 capitalize">{{ str_replace('_', ' ', $key) }}</dt><dd class="text-gray-700">{{ $value }}</dd></div>
                @endforeach
            </dl>
            @else
            <p class="text-xs text-gray-400">Not provided</p>
            @endif
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Shipping Address</p>
            @if (!empty($m['shipping_address']))
            <dl class="text-xs space-y-1">
                @foreach ($m['shipping_address'] as $key => $value)
                <div class="flex gap-2"><dt class="text-gray-400 w-20 shrink-0 capitalize">{{ str_replace('_', ' ', $key) }}</dt><dd class="text-gray-700">{{ $value }}</dd></div>
                @endforeach
            </dl>
            @else
            <p class="text-xs text-gray-400">Same as billing / not provided</p>
            @endif
        </div>
    </div>

    <div>
        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Line Items ({{ count($m['items']) }})</p>
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-xs">
                <thead class="bg-white text-gray-500">
                    <tr>
                        <th class="px-2 py-1.5 text-left">Item</th>
                        <th class="px-2 py-1.5 text-right">Qty</th>
                        <th class="px-2 py-1.5 text-right">Price</th>
                        <th class="px-2 py-1.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($m['items'] as $line)
                    <tr class="{{ $line['sku'] === '' ? 'text-red-500' : '' }}">
                        <td class="px-2 py-1.5">
                            {{ $line['name'] }}
                            <span class="block font-mono text-[10px] text-gray-400">{{ $line['sku'] ?: 'no SKU - will not match a product' }}</span>
                        </td>
                        <td class="px-2 py-1.5 text-right">{{ $line['quantity'] }}</td>
                        <td class="px-2 py-1.5 text-right">Rs. {{ number_format($line['unit_price'], 2) }}</td>
                        <td class="px-2 py-1.5 text-right">Rs. {{ number_format($line['total_price'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-[11px] text-gray-400 mt-1">Lines with no SKU (shown in red) definitely won't match a product and will be skipped on commit. A line with a SKU can still fail to match if that product hasn't been imported yet - import products before orders.</p>
    </div>
</div>

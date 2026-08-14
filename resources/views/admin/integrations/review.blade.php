@extends('layouts.admin')

@section('title', 'Review Import')
@section('page-title', 'Review Import - ' . ucfirst($batch->type))

@section('content')
<div class="space-y-4">

    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-900">
        <i class="fas fa-info-circle mr-1"></i>
        {{ $batch->fetched_count }} {{ $batch->type }} fetched from WooCommerce. Every field that will be written to
        WSRetail is shown below - click a row to expand its full detail before deciding. Uncheck anything you don't
        want, then confirm. Nothing has been added to WSRetail yet.
    </div>

    <form method="POST" action="{{ route('admin.integrations.imports.commit', $batch) }}">
        @csrf
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-4 sm:px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" onclick="document.querySelectorAll('.import-row').forEach(cb => cb.checked = this.checked)" class="w-4 h-4 rounded border-gray-300">
                    Select / deselect all
                </label>
                <span class="text-xs text-gray-400">{{ $items->count() }} item(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="py-2 px-3 w-8"></th>
                            <th class="py-2 px-3 w-8"></th>
                            <th class="py-2 px-3">
                                @if ($batch->type === 'products') Product
                                @elseif ($batch->type === 'customers') Customer
                                @elseif ($batch->type === 'orders') Order
                                @else Setting @endif
                            </th>
                            <th class="py-2 px-3">Details</th>
                            <th class="py-2 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    @foreach ($items as $item)
                    @php $m = $item->mapped_payload; @endphp
                    <tbody x-data="{ open: false }" class="divide-y divide-gray-100 border-b border-gray-100">
                        <tr :class="open ? 'bg-gray-50' : ''" class="{{ $item->action === 'skip' ? 'opacity-50' : '' }}">
                            <td class="py-2 px-3">
                                <input type="checkbox" name="included[]" value="{{ $item->id }}" class="import-row w-4 h-4 rounded border-gray-300"
                                    {{ $item->included && $item->action !== 'skip' ? 'checked' : '' }}
                                    {{ $item->action === 'skip' ? 'disabled' : '' }}>
                            </td>
                            <td class="py-2 px-3">
                                <button type="button" @click="open = !open" class="text-gray-400 hover:text-gray-700">
                                    <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                </button>
                            </td>

                            @if ($batch->type === 'products')
                            <td class="py-2 px-3">
                                <div class="flex items-center gap-2">
                                    @if (!empty($m['images'][0]['url']))
                                        <img src="{{ $m['images'][0]['url'] }}" alt="" class="w-8 h-8 rounded object-cover border border-gray-200 shrink-0">
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $m['name'] }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $m['code'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-3 text-xs text-gray-600">
                                @if (!empty($m['has_variants']))
                                    {{ count($m['variants']) }} variant(s) &middot; {{ implode('/', $m['variant_attribute_names']) }}
                                @else
                                    Rs. {{ number_format($m['sale_price'], 2) }} &middot; Stock: {{ $m['current_stock'] }}
                                @endif
                                @if ($m['category_name']) &middot; {{ $m['category_name'] }} @endif
                                @if (count($m['images']) > 1) &middot; {{ count($m['images']) }} images @endif
                            </td>
                            @elseif ($batch->type === 'customers')
                            <td class="py-2 px-3">
                                <p class="font-medium text-gray-900">{{ $m['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $m['email'] ?? '-' }}</p>
                            </td>
                            <td class="py-2 px-3 text-xs text-gray-600">{{ $m['phone'] ?? 'No phone' }} @if($m['city']) &middot; {{ $m['city'] }} @endif</td>
                            @elseif ($batch->type === 'orders')
                            <td class="py-2 px-3">
                                <p class="font-medium text-gray-900">#{{ $m['order_number'] }}</p>
                                <p class="text-xs text-gray-400">{{ $m['customer']['name'] }}</p>
                            </td>
                            <td class="py-2 px-3 text-xs text-gray-600">
                                Rs. {{ number_format($m['total_amount'], 2) }} &middot; {{ count($m['items']) }} item(s) &middot; {{ $m['sale_date'] }}
                            </td>
                            @else
                            <td class="py-2 px-3">
                                <p class="font-medium text-gray-900">
                                    @if ($m['kind'] === 'store_info') Store Details
                                    @elseif ($m['kind'] === 'payment_gateway') {{ $m['name'] }}
                                    @else Shipping @endif
                                </p>
                                <p class="text-xs text-gray-400">
                                    @if ($m['kind'] === 'store_info') General settings
                                    @elseif ($m['kind'] === 'payment_gateway') Payment method
                                    @else Flat rate + free-shipping threshold @endif
                                </p>
                            </td>
                            <td class="py-2 px-3 text-xs text-gray-600">
                                @if ($m['kind'] === 'store_info')
                                    {{ count($m['fields']) }} field(s)
                                @elseif ($m['kind'] === 'payment_gateway')
                                    {{ $m['is_enabled'] ? 'Enabled on old store' : 'Disabled on old store' }}
                                @else
                                    Rs. {{ number_format($m['shipping_flat_rate'], 2) }} flat
                                    @if ($m['shipping_free_threshold']) &middot; free above Rs. {{ number_format($m['shipping_free_threshold'], 2) }} @endif
                                @endif
                            </td>
                            @endif

                            <td class="py-2 px-3 text-center">
                                @if ($item->action === 'create')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">New</span>
                                @elseif ($item->action === 'update')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800">Update existing</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500">Already imported</span>
                                @endif
                            </td>
                        </tr>

                        <tr x-show="open" x-cloak style="display: none;">
                            <td colspan="5" class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                                @if ($batch->type === 'products')
                                    @include('admin.integrations.partials.review-product', ['m' => $m])
                                @elseif ($batch->type === 'customers')
                                    @include('admin.integrations.partials.review-customer', ['m' => $m])
                                @elseif ($batch->type === 'orders')
                                    @include('admin.integrations.partials.review-order', ['m' => $m])
                                @else
                                    @include('admin.integrations.partials.review-setting', ['m' => $m])
                                @endif
                            </td>
                        </tr>
                    </tbody>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                <i class="fas fa-check mr-1"></i> Confirm Import
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.integrations.imports.cancel', $batch) }}" class="inline">
        @csrf
        <button class="text-sm text-red-600 hover:underline">Cancel this import</button>
    </form>

</div>
@endsection

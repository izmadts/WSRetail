@extends('layouts.admin')

@section('title', 'Print Barcode Labels')
@section('page-title', 'Products')

@section('content')
<div x-data="{ search: '' }" class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-wrap gap-3">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-barcode text-blue-600 mr-2"></i> Print Barcode Labels</h4>
        <a href="{{ route('admin.settings.pos.edit') }}" class="text-sm text-blue-600 hover:underline"><i class="fas fa-cog mr-1"></i> Label Settings</a>
    </div>

    @if(session('error'))
    <div class="mx-6 mt-4 p-3 bg-red-50 border-l-4 border-red-400 rounded-r-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.products.barcode.print') }}" method="POST" target="_blank">
        @csrf
        <div class="p-6">
            <div class="relative max-w-sm mb-4">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" x-model="search" placeholder="Search product name or code..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-100 rounded-lg">
                <table class="w-full">
                    <thead class="sticky top-0 bg-gray-50">
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-3">Product</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-3">Code</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-3">Price</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-3 w-32">Labels</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($products as $product)
                        <tr x-show="!search || {{ \Illuminate\Support\Js::from(strtolower($product->name.' '.$product->code)) }}.includes(search.toLowerCase())" class="hover:bg-gray-50">
                            <td class="py-2 px-3 font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="py-2 px-3 text-sm text-gray-500">{{ $product->barcode ?: $product->code }}</td>
                            <td class="py-2 px-3 text-right text-sm text-gray-600">Rs. {{ number_format($product->sale_price, 2) }}</td>
                            <td class="py-2 px-3 text-center">
                                <input type="number" name="qty[p{{ $product->id }}]" value="0" min="0" max="500"
                                    class="w-20 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg">
                            </td>
                        </tr>
                        @endforeach
                        @foreach($variants as $variant)
                        <tr x-show="!search || {{ \Illuminate\Support\Js::from(strtolower(($variant->product->name ?? '').' '.$variant->label.' '.$variant->sku)) }}.includes(search.toLowerCase())" class="hover:bg-gray-50">
                            <td class="py-2 px-3 font-medium text-gray-900">
                                {{ $variant->product->name ?? 'N/A' }}
                                <span class="ml-1 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs">{{ $variant->label }}</span>
                            </td>
                            <td class="py-2 px-3 text-sm text-gray-500">{{ $variant->barcode ?: $variant->sku }}</td>
                            <td class="py-2 px-3 text-right text-sm text-gray-600">Rs. {{ number_format($variant->sale_price, 2) }}</td>
                            <td class="py-2 px-3 text-center">
                                <input type="number" name="qty[v{{ $variant->id }}]" value="0" min="0" max="500"
                                    class="w-20 px-2 py-1 text-sm text-center border border-gray-300 rounded-lg">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-print mr-1"></i> Generate Labels
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Ecommerce Settings - Shipping')
@section('page-title', 'Ecommerce Settings')

@section('content')
@include('admin.settings.partials.tabs')
@include('admin.ecommerce.partials.tabs')

@if (session('success'))
    <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm mb-6 max-w-2xl">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.settings.ecommerce.shipping.update') }}" method="POST" class="space-y-6 max-w-2xl">
    @csrf

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-truck text-purple-600 mr-2"></i> Shipping</h4>
            <p class="mt-1 text-sm text-gray-500">
                A single flat rate applied to every order placed through the Customer API (storefront checkout), with
                an optional free-shipping threshold. Not per-zone/per-city rates - orders created directly in the
                admin (POS/manual sales) are unaffected, since shipping doesn't apply to in-person retail.
            </p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Flat Shipping Rate
                    <x-help-tooltip>Added to every storefront order's total. Set to 0 for always-free shipping.</x-help-tooltip>
                </label>
                <input type="number" step="0.01" min="0" max="100000" name="shipping_flat_rate" value="{{ old('shipping_flat_rate', $settings['shipping_flat_rate']) }}" required placeholder="e.g. 150"
                    class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('shipping_flat_rate')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Free Shipping Threshold
                    <x-help-tooltip>Orders with a subtotal at or above this amount get free shipping automatically. Leave blank to disable - the flat rate always applies.</x-help-tooltip>
                </label>
                <input type="number" step="0.01" min="0" max="10000000" name="shipping_free_threshold" value="{{ old('shipping_free_threshold', $settings['shipping_free_threshold']) }}" placeholder="e.g. 3000 - leave blank to disable"
                    class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('shipping_free_threshold')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors duration-200">
            <i class="fas fa-save mr-1"></i> Save Shipping Settings
        </button>
    </div>
</form>
@endsection

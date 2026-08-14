@extends('layouts.admin')

@section('title', 'Ecommerce Settings - Store')
@section('page-title', 'Ecommerce Settings')

@section('content')
@include('admin.settings.partials.tabs')
@include('admin.ecommerce.partials.tabs')

@if (session('success'))
    <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm mb-6 max-w-2xl">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.settings.ecommerce.store.update') }}" method="POST" class="space-y-6 max-w-2xl">
    @csrf

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-store text-purple-600 mr-2"></i> Store Details</h4>
            <p class="mt-1 text-sm text-gray-500">
                Storefront-facing details - shown on receipts/order confirmations and used to build SEO redirect
                links (Settings &gt; Integrations &gt; SEO Redirects). App name, currency, and logo stay in
                <a href="{{ route('admin.settings.general') }}" class="text-purple-600 hover:underline">General Settings</a>.
            </p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Storefront URL
                    <x-help-tooltip>The public URL of your customer-facing storefront (e.g. the Next.js app), such as https://shop.yourdomain.com. Used to build full links in SEO redirect exports and elsewhere a customer-facing URL is needed.</x-help-tooltip>
                </label>
                <input type="url" name="storefront_url" value="{{ old('storefront_url', $settings['storefront_url']) }}" placeholder="https://shop.yourdomain.com" maxlength="255"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('storefront_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Store Address</label>
                <textarea name="store_address" rows="2" maxlength="500" placeholder="e.g. 123 Mall Road, Lahore, Pakistan"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('store_address', $settings['store_address']) }}</textarea>
                @error('store_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                    <input type="tel" name="store_phone" value="{{ old('store_phone', $settings['store_phone']) }}" placeholder="+92 300 1234567" maxlength="30"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('store_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="store_email" value="{{ old('store_email', $settings['store_email']) }}" placeholder="support@yourdomain.com" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('store_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors duration-200">
            <i class="fas fa-save mr-1"></i> Save Store Settings
        </button>
    </div>
</form>
@endsection

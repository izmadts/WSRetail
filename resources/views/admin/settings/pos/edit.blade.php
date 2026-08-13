@extends('layouts.admin')

@section('title', 'Settings - POS')
@section('page-title', 'Settings')

@php
    $paymentMethodLabels = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'credit_card' => 'Credit Card',
    ];
    $enabledMethods = old('payment_methods', $posSetting->payment_methods ?? ['cash']);
@endphp

@section('content')
@include('admin.settings.partials.tabs')

<form action="{{ route('admin.settings.pos.update') }}" method="POST" class="space-y-6 max-w-4xl">
    @csrf

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-sliders-h text-blue-600 mr-2"></i> POS Defaults</h4>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Default Location
                    <x-help-tooltip>Preselected when an admin/manager (not locked to a location) opens POS fresh. A POS Manager locked to one location always ignores this - their own fixed location wins.</x-help-tooltip>
                </label>
                <select name="default_location_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">None</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ old('default_location_id', $posSetting->default_location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Default Customer</label>
                <select name="default_customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">None</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('default_customer_id', $posSetting->default_customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Products Shown Per Page
                    <x-help-tooltip>How many products the POS product grid shows before a "Load more" click.</x-help-tooltip>
                </label>
                <input type="number" name="products_per_page" value="{{ old('products_per_page', $posSetting->products_per_page) }}" min="6" max="200" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('products_per_page')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-receipt text-blue-600 mr-2"></i> Receipt / Printer</h4>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Receipt Paper Size
                        <x-help-tooltip>Controls the printed receipt's width. Thermal sizes match common receipt printers (58mm/80mm rolls); A4 prints a normal full-page invoice instead.</x-help-tooltip>
                    </label>
                    <select name="invoice_paper_size" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="thermal_58" {{ old('invoice_paper_size', $posSetting->invoice_paper_size) == 'thermal_58' ? 'selected' : '' }}>Thermal 58mm</option>
                        <option value="thermal_80" {{ old('invoice_paper_size', $posSetting->invoice_paper_size) == 'thermal_80' ? 'selected' : '' }}>Thermal 80mm</option>
                        <option value="a4" {{ old('invoice_paper_size', $posSetting->invoice_paper_size) == 'a4' ? 'selected' : '' }}>A4</option>
                    </select>
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_print_receipt" value="1" {{ old('auto_print_receipt', $posSetting->auto_print_receipt) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">Auto-open print dialog after checkout</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Methods Available at POS</label>
                <div class="flex flex-wrap gap-4">
                    @foreach($paymentMethodLabels as $value => $label)
                    <label class="flex items-center">
                        <input type="checkbox" name="payment_methods[]" value="{{ $value }}" {{ in_array($value, $enabledMethods) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-barcode text-blue-600 mr-2"></i> Barcode Labels</h4>
            <a href="{{ route('admin.products.barcode.index') }}" class="text-sm text-blue-600 hover:underline"><i class="fas fa-print mr-1"></i> Print Labels</a>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode Format</label>
                <select name="barcode_format" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="CODE128" {{ old('barcode_format', $posSetting->barcode_format) == 'CODE128' ? 'selected' : '' }}>CODE128</option>
                    <option value="EAN13" {{ old('barcode_format', $posSetting->barcode_format) == 'EAN13' ? 'selected' : '' }}>EAN13</option>
                    <option value="CODE39" {{ old('barcode_format', $posSetting->barcode_format) == 'CODE39' ? 'selected' : '' }}>CODE39</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Width (mm)</label>
                <input type="number" step="0.1" name="barcode_label_width_mm" value="{{ old('barcode_label_width_mm', $posSetting->barcode_label_width_mm) }}" min="10" max="200" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Height (mm)</label>
                <input type="number" step="0.1" name="barcode_label_height_mm" value="{{ old('barcode_label_height_mm', $posSetting->barcode_label_height_mm) }}" min="10" max="200" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Columns Per Row</label>
                <input type="number" name="barcode_columns_per_row" value="{{ old('barcode_columns_per_row', $posSetting->barcode_columns_per_row) }}" min="1" max="10" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="lg:col-span-4 flex gap-6">
                <label class="flex items-center">
                    <input type="checkbox" name="barcode_show_name" value="1" {{ old('barcode_show_name', $posSetting->barcode_show_name) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Show product name on label</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="barcode_show_price" value="1" {{ old('barcode_show_price', $posSetting->barcode_show_price) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Show price on label</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-save mr-1"></i> Save POS Settings
        </button>
    </div>
</form>
@endsection

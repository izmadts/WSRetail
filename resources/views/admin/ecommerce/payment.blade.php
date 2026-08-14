@extends('layouts.admin')

@section('title', 'Ecommerce Settings - Payment')
@section('page-title', 'Ecommerce Settings')

@section('content')
@include('admin.settings.partials.tabs')
@include('admin.ecommerce.partials.tabs')

@if (session('success'))
    <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm mb-6 max-w-3xl">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm mb-6 max-w-3xl">
        <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="max-w-3xl space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-900">
        <i class="fas fa-info-circle mr-1"></i> These are the checkout options shown to shoppers on your storefront -
        not a payment gateway. No card/online payment is processed here; a shopper's pick is recorded as free text on
        their order (<code>payment_method</code>), same as it always has been.
    </div>

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-credit-card text-purple-600 mr-2"></i> Payment Methods</h4>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse ($methods as $method)
            <form action="{{ route('admin.settings.ecommerce.payment.update', $method) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex-1">
                        <input type="text" name="name" value="{{ old('name', $method->name) }}" required minlength="2" maxlength="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                        <p class="text-xs text-gray-400 mt-1">Code: <code>{{ $method->code }}</code></p>
                    </div>
                    <label class="flex items-center gap-2 shrink-0 pt-2">
                        <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $method->is_enabled) ? 'checked' : '' }}
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">Enabled</span>
                    </label>
                </div>
                <textarea name="description" rows="2" maxlength="1000" placeholder="Shown to the shopper at checkout - e.g. your bank account details for Bank Transfer"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-3">{{ old('description', $method->description) }}</textarea>
                <div class="flex items-center justify-between">
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <button type="submit" form="delete-method-{{ $method->id }}" onclick="return confirm('Remove this payment method?')"
                        class="text-red-600 hover:text-red-800 text-xs font-medium">
                        <i class="fas fa-trash mr-1"></i> Remove
                    </button>
                </div>
            </form>
            <form id="delete-method-{{ $method->id }}" action="{{ route('admin.settings.ecommerce.payment.destroy', $method) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @empty
            <div class="p-10 text-center text-sm text-gray-400">No payment methods yet - add one below.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900"><i class="fas fa-plus text-purple-600 mr-2"></i> Add Payment Method</h4>
        </div>
        <form action="{{ route('admin.settings.ecommerce.payment.store') }}" method="POST" class="p-6 space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Name *</label>
                <input type="text" name="new_name" required minlength="2" maxlength="100" placeholder="e.g. JazzCash Manual Transfer" value="{{ old('new_name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Description (shown at checkout)</label>
                <textarea name="new_description" rows="2" maxlength="1000" placeholder="e.g. Send payment to 0300-1234567 and share the screenshot on WhatsApp." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('new_description') }}</textarea>
            </div>
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
                <i class="fas fa-plus mr-1"></i> Add Payment Method
            </button>
        </form>
    </div>
</div>
@endsection

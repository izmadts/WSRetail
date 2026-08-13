@extends('layouts.admin')

@section('title', 'Product Attributes')
@section('page-title', 'Product Attributes')

@section('content')
<div class="space-y-4">

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
        <i class="fas fa-info-circle mr-1"></i>
        Attributes (Size, Color, Storage...) are reused across products. You can also add new attributes and
        values directly while creating a product - this page is just for managing them ahead of time.
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="POST" action="{{ route('admin.product-attributes.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="name" required placeholder="New attribute, e.g. Size"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i> Add Attribute
            </button>
        </form>
        @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse ($attributes as $attribute)
        <div class="bg-white rounded-xl shadow-card p-4">
            <h3 class="font-semibold text-gray-900 mb-2">{{ $attribute->name }}</h3>
            <div class="flex flex-wrap gap-1.5 mb-3">
                @forelse ($attribute->values as $value)
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs">{{ $value->value }}</span>
                @empty
                    <span class="text-xs text-gray-400">No values yet.</span>
                @endforelse
            </div>
            <form method="POST" action="{{ route('admin.product-attributes.values.store', $attribute) }}" class="flex gap-2">
                @csrf
                <input type="text" name="value" required placeholder="Add a value, e.g. XL"
                       class="flex-1 px-2 py-1.5 border border-gray-300 rounded-lg text-xs">
                <button class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-medium hover:bg-gray-800">Add</button>
            </form>
        </div>
        @empty
        <p class="text-gray-400 text-sm">No attributes yet - add one above (e.g. Size, Color, Storage).</p>
        @endforelse
    </div>
</div>
@endsection

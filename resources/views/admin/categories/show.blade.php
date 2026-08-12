@extends('layouts.admin')

@section('title', 'Category Details')
@section('page-title', 'Category: ' . $category->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Category Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tags text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $category->name }}</h3>
                <p class="text-sm text-gray-500">{{ $category->parent->name ?? 'Main Category' }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Slug</span>
                        <span class="font-medium text-gray-900">{{ $category->slug }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Products</span>
                        <span class="font-medium text-gray-900">{{ $category->products->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Created</span>
                        <span class="font-medium text-gray-900">{{ $category->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex space-x-2">
                <a href="{{ route('admin.categories.edit', $category) }}"
                    class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('admin.categories.index') }}"
                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Products in Category -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-box text-gray-400 mr-2"></i> Products in this Category
                    <span class="ml-2 text-sm text-gray-500">{{ $category->products->count() }} total</span>
                </h4>
            </div>
            <div class="p-6">
                @if($category->products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($category->products as $product)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-card-hover transition-shadow duration-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h5 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h5>
                                <p class="text-xs text-gray-500">{{ $product->code }}</p>
                                <div class="mt-2 flex items-center space-x-3">
                                    <span class="text-xs text-gray-600">Unit: {{ $product->unit }}</span>
                                    <span class="text-xs font-medium text-gray-900">Rs. {{ number_format($product->sale_price, 2) }}</span>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $product->isLowStock() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ number_format($product->current_stock, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <a href="{{ route('admin.products.show', $product) }}" class="text-xs text-blue-600 hover:text-blue-800">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-xs text-yellow-600 hover:text-yellow-800">Edit</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 py-8">
                    <i class="fas fa-inbox text-3xl text-gray-300 mb-2 block"></i>
                    No products in this category.
                </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
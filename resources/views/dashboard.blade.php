@extends('layouts.gue')

@section('title', 'Dashboard')
@section('page-title', 'Overview')

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-card hover:shadow-card-hover transition-shadow duration-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Products</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-box text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card hover:shadow-card-hover transition-shadow duration-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Active Products</p>
                <p class="text-2xl font-bold text-gray-900">{{ $activeProducts }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card hover:shadow-card-hover transition-shadow duration-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Low Stock</p>
                <p class="text-2xl font-bold text-red-600">{{ $lowStockProducts }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-card hover:shadow-card-hover transition-shadow duration-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Categories</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCategories }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-tags text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <a href="{{ route('admin.products.create') }}"
        class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-plus text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold">Add New Product</h3>
                <p class="text-blue-100 text-sm">Create new product in inventory</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.products.low-stock') }}"
        class="bg-gradient-to-r from-red-600 to-red-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-truck text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold">Restock Alert</h3>
                <p class="text-red-100 text-sm">{{ $lowStockProducts }} products below minimum</p>
            </div>
        </div>
    </a>

    <a href="#"
        class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-shopping-cart text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold">New Sale</h3>
                <p class="text-green-100 text-sm">Create a new sales invoice</p>
            </div>
        </div>
    </a>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-xl shadow-card">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-clock text-gray-400 mr-2"></i> Recent Activity
        </h3>
    </div>
    <div class="p-6 text-center py-12 text-gray-500">
        <i class="fas fa-database text-4xl mb-3 text-gray-300"></i>
        <p>System is ready. Start adding products and transactions.</p>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Overview')

@section('content')
<div class="space-y-6">

    <!-- ========================================== -->
    <!-- STATS CARDS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Sales</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($currentMonthSales, 2) }}</p>
                    <p class="text-xs {{ $salesGrowth >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        <i class="fas {{ $salesGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ number_format($salesGrowth, 1) }}% from last month
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Purchases</p>
                    <p class="text-2xl font-bold text-gray-900">Rs. {{ number_format($currentMonthPurchases, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Customers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalCustomers }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ $activeCustomers }} active</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Products</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
                    <p class="text-xs {{ $lowStockProducts > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        {{ $lowStockProducts }} low stock
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- QUICK ACTION BUTTONS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.sales.create') }}" 
           class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-4 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold">New Sale</h4>
                    <p class="text-xs text-blue-100">Create invoice</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.purchases.create') }}" 
           class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg p-4 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-truck text-xl"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold">New Purchase</h4>
                    <p class="text-xs text-purple-100">Order stock</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.products.create') }}" 
           class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-4 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold">Add Product</h4>
                    <p class="text-xs text-green-100">New inventory</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.customers.create') }}" 
           class="bg-gradient-to-r from-yellow-600 to-yellow-700 rounded-xl shadow-lg p-4 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-plus text-xl"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-semibold">Add Customer</h4>
                    <p class="text-xs text-yellow-100">New client</p>
                </div>
            </div>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- TWO COLUMN: RECENT ACTIVITY -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-bag text-blue-600 mr-2"></i> Recent Sales
                </h4>
                <a href="{{ route('admin.sales.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="p-4">
                @if($recentSales->count() > 0)
                    @foreach($recentSales as $sale)
                    <div class="border-b border-gray-100 py-3 last:border-0">
                        <div class="flex justify-between items-center">
                            <div>
                                <a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline text-sm font-medium">
                                    {{ $sale->invoice_no }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $sale->customer->name ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">Rs. {{ number_format($sale->total_amount, 2) }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">
                                    {{ $sale->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-500 py-6">No recent sales</p>
                @endif
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-cart text-purple-600 mr-2"></i> Recent Purchases
                </h4>
                <a href="{{ route('admin.purchases.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="p-4">
                @if($recentPurchases->count() > 0)
                    @foreach($recentPurchases as $purchase)
                    <div class="border-b border-gray-100 py-3 last:border-0">
                        <div class="flex justify-between items-center">
                            <div>
                                <a href="{{ route('admin.purchases.show', $purchase) }}" class="text-blue-600 hover:underline text-sm font-medium">
                                    {{ $purchase->invoice_no }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $purchase->supplier->name ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">Rs. {{ number_format($purchase->total_amount, 2) }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $purchase->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-500 py-6">No recent purchases</p>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- THREE COLUMN: Quick Stats -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Low Stock Products -->
        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Low Stock Products
                </h4>
                <a href="{{ route('admin.products.low-stock') }}" class="text-xs text-blue-600 hover:underline">View</a>
            </div>
            @php $lowStockItems = App\Models\Product::with('category')->lowStock()->limit(5)->get(); @endphp
            @if($lowStockItems->count() > 0)
                @foreach($lowStockItems as $product)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                    <span class="text-sm text-gray-700">{{ $product->name }}</span>
                    <span class="text-xs font-semibold text-red-600">{{ number_format($product->current_stock, 2) }}</span>
                </div>
                @endforeach
            @else
                <p class="text-sm text-green-600 text-center py-2">✅ All products at safe levels</p>
            @endif
        </div>

        <!-- Pending Agent Approvals -->
        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-user-tie text-yellow-500 mr-2"></i> Pending Approvals
                </h4>
                <a href="{{ route('admin.agents.pending') }}" class="text-xs text-blue-600 hover:underline">View</a>
            </div>
            @php $pendingAgents = App\Models\User::where('role', 'sales_agent')->where('is_active', false)->whereNull('approved_at')->limit(5)->get(); @endphp
            @if($pendingAgents->count() > 0)
                @foreach($pendingAgents as $agent)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                    <span class="text-sm text-gray-700">{{ $agent->name }}</span>
                    <span class="text-xs text-yellow-600">{{ $agent->created_at->format('d-m-Y') }}</span>
                </div>
                @endforeach
            @else
                <p class="text-sm text-green-600 text-center py-2">✅ No pending approvals</p>
            @endif
        </div>

        <!-- Today's Summary -->
        <div class="bg-white rounded-xl shadow-card p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-calendar-day text-blue-500 mr-2"></i> Today's Summary
            </h4>
            <div class="space-y-2">
                @php
                    $todaySales = App\Models\Sale::whereDate('sale_date', today())->sum('total_amount');
                    $todayPurchases = App\Models\Purchase::whereDate('purchase_date', today())->sum('total_amount');
                    $todayCustomers = App\Models\Customer::whereDate('created_at', today())->count();
                @endphp
                <div class="flex justify-between items-center py-1">
                    <span class="text-sm text-gray-600">Sales</span>
                    <span class="text-sm font-semibold text-blue-600">Rs. {{ number_format($todaySales, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-sm text-gray-600">Purchases</span>
                    <span class="text-sm font-semibold text-purple-600">Rs. {{ number_format($todayPurchases, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-sm text-gray-600">New Customers</span>
                    <span class="text-sm font-semibold text-green-600">{{ $todayCustomers }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
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
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Total Sales</p>
                    <p class="text-2xl font-bold text-gray-900 truncate" title="Rs. {{ number_format($currentMonthSales, 2) }}">
                        Rs. {{ \App\Helpers\NumberHelper::compact($currentMonthSales) }}
                    </p>
                    <p class="text-xs {{ $salesGrowth >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        <i class="fas {{ $salesGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ number_format($salesGrowth, 1) }}% from last month
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Total Purchases</p>
                    <p class="text-2xl font-bold text-gray-900 truncate" title="Rs. {{ number_format($currentMonthPurchases, 2) }}">
                        Rs. {{ \App\Helpers\NumberHelper::compact($currentMonthPurchases) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Customers</p>
                    <p class="text-2xl font-bold text-gray-900 truncate" title="{{ number_format($totalCustomers) }}">
                        {{ \App\Helpers\NumberHelper::compact($totalCustomers) }}
                    </p>
                    <p class="text-xs text-green-600 mt-1">{{ \App\Helpers\NumberHelper::compact($activeCustomers) }} active</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Products</p>
                    <p class="text-2xl font-bold text-gray-900 truncate" title="{{ number_format($totalProducts) }}">
                        {{ \App\Helpers\NumberHelper::compact($totalProducts) }}
                    </p>
                    <p class="text-xs {{ $lowStockProducts > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        {{ \App\Helpers\NumberHelper::compact($lowStockProducts) }} low stock
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
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
    <!-- MONTHLY SALES & PURCHASES CHART -->
    <!-- ========================================== -->
    <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i> Monthly Overview
            </h3>
            <span class="text-xs text-gray-500">Last 12 months</span>
        </div>
        <div style="height: 300px; position: relative;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TWO COLUMN CHARTS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-crown text-yellow-500 mr-2"></i> Top Products
            </h3>
            <div style="height: 250px; position: relative;">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-credit-card text-green-600 mr-2"></i> Payment Status
            </h3>
            <div style="height: 250px; position: relative;">
                <canvas id="paymentStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- DAILY SALES CHART -->
    <!-- ========================================== -->
    <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-calendar-day text-blue-600 mr-2"></i> Daily Sales (Last 30 Days)
            </h3>
            <span class="text-xs text-gray-500">30 days trend</span>
        </div>
        <div style="height: 250px; position: relative;">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- RECENT ACTIVITIES -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                    <p class="text-center text-gray-500 py-4">No recent sales</p>
                @endif
            </div>
        </div>

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
                    <p class="text-center text-gray-500 py-4">No recent purchases</p>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loaded - Creating charts...');

    // =============================================
    // CHECK IF CHART.JS IS LOADED
    // =============================================
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded!');
        return;
    }
    console.log('Chart.js version:', Chart.version);

    // =============================================
    // COLORS
    // =============================================
    var colors = {
        blue: 'rgba(59, 130, 246, 1)',
        blueLight: 'rgba(59, 130, 246, 0.2)',
        green: 'rgba(34, 197, 94, 1)',
        greenLight: 'rgba(34, 197, 94, 0.2)',
        purple: 'rgba(139, 92, 246, 1)',
        purpleLight: 'rgba(139, 92, 246, 0.2)',
        red: 'rgba(239, 68, 68, 1)',
        redLight: 'rgba(239, 68, 68, 0.2)',
        yellow: 'rgba(234, 179, 8, 1)',
        yellowLight: 'rgba(234, 179, 8, 0.2)',
        orange: 'rgba(249, 115, 22, 1)',
        orangeLight: 'rgba(249, 115, 22, 0.2)',
        teal: 'rgba(20, 184, 166, 1)',
        tealLight: 'rgba(20, 184, 166, 0.2)',
    };

    // =============================================
    // 1. MONTHLY SALES & PURCHASES CHART
    // =============================================
    var monthlyEl = document.getElementById('monthlyChart');
    if (monthlyEl) {
        var monthlySales = {!! json_encode($monthlySales) !!};
        var monthlyPurchases = {!! json_encode($monthlyPurchases) !!};
        var months = {!! json_encode($months) !!};

        if (!monthlySales.length) {
            monthlySales = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }
        if (!months.length) {
            months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        }

        new Chart(monthlyEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Sales',
                    data: monthlySales,
                    backgroundColor: colors.blueLight,
                    borderColor: colors.blue,
                    borderWidth: 2,
                    borderRadius: 4,
                }, {
                    label: 'Purchases',
                    data: monthlyPurchases,
                    backgroundColor: colors.purpleLight,
                    borderColor: colors.purple,
                    borderWidth: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });
        console.log('Monthly chart created');
    }

    // =============================================
    // 2. TOP PRODUCTS CHART
    // =============================================
    var topEl = document.getElementById('topProductsChart');
    if (topEl) {
        var topLabels = {!! json_encode($topProducts->pluck('name')) !!};
        var topData = {!! json_encode($topProducts->pluck('total_quantity')) !!};

        if (!topLabels.length) {
            topLabels = ['No Data'];
            topData = [1];
        }

        new Chart(topEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: topLabels,
                datasets: [{
                    data: topData,
                    backgroundColor: [
                        '#3B82F6', '#22C55E', '#8B5CF6', '#F97316', '#EF4444',
                        '#14B8A6', '#EAB308', '#8B5CF6', '#EC4899', '#14B8A6'
                    ],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
        console.log('Top products chart created');
    }

    // =============================================
    // 3. PAYMENT STATUS CHART
    // =============================================
    var paymentEl = document.getElementById('paymentStatusChart');
    if (paymentEl) {
        var paid = {!! json_encode($paidSales) !!};
        var partial = {!! json_encode($partialSales) !!};
        var pending = {!! json_encode($pendingSales) !!};
        var draft = {!! json_encode($draftSales) !!};
        var hasData = paid > 0 || partial > 0 || pending > 0 || draft > 0;

        new Chart(paymentEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Partial', 'Confirmed', 'Draft'],
                datasets: [{
                    data: hasData ? [paid, partial, pending, draft] : [1, 0, 0, 0],
                    backgroundColor: hasData ? ['#22C55E', '#EAB308', '#3B82F6', '#9CA3AF'] : ['#D1D5DB'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
        console.log('Payment status chart created');
    }

    // =============================================
    // 4. DAILY SALES CHART
    // =============================================
    var dailyEl = document.getElementById('dailySalesChart');
    if (dailyEl) {
        var dailyLabels = {!! json_encode($dailyLabels) !!};
        var dailyData = {!! json_encode($dailySales) !!};

        if (!dailyLabels.length) {
            dailyLabels = ['No Data'];
            dailyData = [0];
        }

        new Chart(dailyEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Daily Sales',
                    data: dailyData,
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });
        console.log('Daily sales chart created');
    }

    console.log('All charts created successfully!');
});
</script>
@endsection
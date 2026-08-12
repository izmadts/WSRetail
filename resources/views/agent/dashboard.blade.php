@extends('layouts.agent')

@section('title', 'Agent Dashboard')
@section('page-title', 'Dashboard')

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
                    <p class="text-2xl font-bold text-gray-900 truncate" title="Rs. {{ number_format($totalSales ?? 0, 2) }}">
                        Rs. {{ \App\Helpers\NumberHelper::compact($totalSales ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $sales->count() ?? 0 }} transactions</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Commission Earned</p>
                    <p class="text-2xl font-bold text-purple-600 truncate" title="Rs. {{ number_format($totalCommission ?? 0, 2) }}">
                        Rs. {{ \App\Helpers\NumberHelper::compact($totalCommission ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1 truncate">This month: Rs. {{ \App\Helpers\NumberHelper::compact($currentMonthCommission ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-coins text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Recovery Rate</p>
                    <p class="text-2xl font-bold {{ ($recoveryRate ?? 0) >= 95 ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ number_format($recoveryRate ?? 0, 2) }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Target: 95%</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-percentage text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">New Customers</p>
                    <p class="text-2xl font-bold text-orange-600 truncate" title="{{ number_format($newCustomers ?? 0) }}">
                        {{ \App\Helpers\NumberHelper::compact($newCustomers ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Total: {{ \App\Helpers\NumberHelper::compact($customers->count() ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user-plus text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- QUICK ACTION BUTTONS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <a href="{{ route('agent.sales.create') }}" 
           class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plus text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">New Sale</h3>
                    <p class="text-blue-100 text-sm">Create invoice</p>
                </div>
            </div>
        </a>

        <a href="{{ route('agent.customers.create') }}" 
           class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">New Customer</h3>
                    <p class="text-green-100 text-sm">Add customer</p>
                </div>
            </div>
        </a>

        <a href="{{ route('agent.commissions.index') }}" 
           class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">Commission</h3>
                    <p class="text-purple-100 text-sm">View earnings</p>
                </div>
            </div>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- CHARTS SECTION -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Sales Chart -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i> Monthly Sales
                </h4>
                <span class="text-xs text-gray-500">Last 6 months</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>

        <!-- Commission Breakdown Chart -->
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-pie text-purple-600 mr-2"></i> Commission Breakdown
                </h4>
                <span class="text-xs text-gray-500">Total: Rs. {{ number_format($totalCommission ?? 0, 2) }}</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="commissionBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- COMMISSION BREAKDOWN CARDS -->
    <!-- ========================================== -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-coins text-gray-400 mr-2"></i> Commission Breakdown
            </h4>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200 min-w-0">
                <p class="text-sm text-gray-500">Sale Commission</p>
                <p class="text-xl font-bold text-blue-600 truncate" title="Rs. {{ number_format($saleCommission ?? 0, 2) }}">
                    Rs. {{ \App\Helpers\NumberHelper::compact($saleCommission ?? 0) }}
                </p>
                <p class="text-xs text-gray-500">{{ $sales->count() ?? 0 }} sales</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200 min-w-0">
                <p class="text-sm text-gray-500">New Customer Bonus</p>
                <p class="text-xl font-bold text-green-600 truncate" title="Rs. {{ number_format($newCustomerBonus ?? 0, 2) }}">
                    Rs. {{ \App\Helpers\NumberHelper::compact($newCustomerBonus ?? 0) }}
                </p>
                <p class="text-xs text-gray-500">{{ $newCustomers ?? 0 }} new customers</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg border border-purple-200 min-w-0">
                <p class="text-sm text-gray-500">Recovery Bonus</p>
                <p class="text-xl font-bold text-purple-600 truncate" title="Rs. {{ number_format($recoveryBonus ?? 0, 2) }}">
                    Rs. {{ \App\Helpers\NumberHelper::compact($recoveryBonus ?? 0) }}
                </p>
                <p class="text-xs text-gray-500">{{ number_format($recoveryRate ?? 0, 2) }}% recovery</p>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200 min-w-0">
                <p class="text-sm text-gray-500">Target Bonus</p>
                <p class="text-xl font-bold text-yellow-600 truncate" title="Rs. {{ number_format($targetBonus ?? 0, 2) }}">
                    Rs. {{ \App\Helpers\NumberHelper::compact($targetBonus ?? 0) }}
                </p>
                <p class="text-xs text-gray-500">Monthly achievement</p>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- RECENT SALES & COMMISSIONS -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-bag text-blue-600 mr-2"></i> Recent Sales
                </h4>
                <a href="{{ route('agent.sales.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="p-4">
                @if($sales->count() > 0)
                    @foreach($sales->take(5) as $sale)
                    <div class="border-b border-gray-100 py-3 last:border-0">
                        <div class="flex justify-between items-center">
                            <div>
                                <a href="{{ route('agent.sales.show', $sale) }}" class="text-blue-600 hover:underline text-sm font-medium">
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
                    <p class="text-center text-gray-500 py-4">No sales yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Commissions -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-coins text-purple-600 mr-2"></i> Recent Commissions
                </h4>
                <a href="{{ route('agent.commissions.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="p-4">
                @if($commissionLogs->count() > 0)
                    @foreach($commissionLogs as $log)
                    <div class="border-b border-gray-100 py-3 last:border-0">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $log->type_label }}</p>
                                <p class="text-xs text-gray-500">{{ $log->created_at->format('d-m-Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-purple-600">+ Rs. {{ number_format($log->amount, 2) }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $log->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $log->is_paid ? 'Paid' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-500 py-4">No commissions yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- CUSTOMER SUMMARY -->
    <!-- ========================================== -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-users text-gray-400 mr-2"></i> My Customers
            </h4>
            <a href="{{ route('agent.customers.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
        </div>
        <div class="p-4">
            @if($customers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($customers->take(6) as $customer)
                    <div class="border border-gray-200 rounded-lg p-3 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $customer->phone ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold {{ $customer->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rs. {{ number_format($customer->balance, 2) }}
                                </p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <a href="{{ route('agent.customers.show', $customer) }}" class="text-xs text-blue-600 hover:underline">View</a>
                            <a href="{{ route('agent.sales.create') }}?customer={{ $customer->id }}" class="text-xs text-green-600 hover:underline">Create Sale</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($customers->count() > 6)
                    <div class="mt-3 text-center">
                        <a href="{{ route('agent.customers.index') }}" class="text-sm text-blue-600 hover:underline">
                            View all {{ $customers->count() }} customers
                        </a>
                    </div>
                @endif
            @else
                <p class="text-center text-gray-500 py-4">No customers yet. <a href="{{ route('agent.customers.create') }}" class="text-blue-600 hover:underline">Add your first customer</a></p>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }

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
        orange: 'rgba(249, 115, 22, 1)',
        orangeLight: 'rgba(249, 115, 22, 0.2)',
        yellow: 'rgba(234, 179, 8, 1)',
        yellowLight: 'rgba(234, 179, 8, 0.2)',
        red: 'rgba(239, 68, 68, 1)',
        redLight: 'rgba(239, 68, 68, 0.2)',
    };

    // =============================================
    // 1. MONTHLY SALES CHART
    // =============================================
    var monthlySalesEl = document.getElementById('monthlySalesChart');
    if (monthlySalesEl) {
        var monthlyLabels = {!! json_encode($monthlyLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!};
        var monthlyData = {!! json_encode($monthlySalesData ?? [0, 0, 0, 0, 0, 0]) !!};

        new Chart(monthlySalesEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Sales',
                    data: monthlyData,
                    backgroundColor: colors.blueLight,
                    borderColor: colors.blue,
                    borderWidth: 2,
                    borderRadius: 4,
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
    }

    // =============================================
    // 2. COMMISSION BREAKDOWN CHART
    // =============================================
    var commissionEl = document.getElementById('commissionBreakdownChart');
    if (commissionEl) {
        var commissionData = {
            sale: {{ $saleCommission ?? 0 }},
            newCustomer: {{ $newCustomerBonus ?? 0 }},
            recovery: {{ $recoveryBonus ?? 0 }},
            target: {{ $targetBonus ?? 0 }}
        };

        var hasCommissionData = commissionData.sale > 0 || commissionData.newCustomer > 0 || 
                               commissionData.recovery > 0 || commissionData.target > 0;

        new Chart(commissionEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Sale Commission', 'New Customer Bonus', 'Recovery Bonus', 'Target Bonus'],
                datasets: [{
                    data: hasCommissionData ? 
                        [commissionData.sale, commissionData.newCustomer, commissionData.recovery, commissionData.target] :
                        [1, 0, 0, 0],
                    backgroundColor: hasCommissionData ?
                        [colors.blue, colors.green, colors.purple, colors.yellow] :
                        ['#D1D5DB'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
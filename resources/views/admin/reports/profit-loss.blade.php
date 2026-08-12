@extends('layouts.admin')

@section('title', 'Profit & Loss Report')
@section('page-title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.reports.profit-loss-pdf', request()->all()) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-1"></i> PDF (Khata Statement)
        </a>
    </div>
    <!-- Date Filter -->
    <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.reports.profit-loss') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Income</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($totalIncome, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">COGS</p>
                    <p class="text-2xl font-bold text-orange-600">Rs. {{ number_format($cogs, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Gross Profit</p>
                    <p class="text-2xl font-bold {{ $grossProfit >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        Rs. {{ number_format($grossProfit, 2) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Net Profit / Loss</p>
                    <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format($netProfit, 2) }}
                    </p>
                </div>
                <div class="w-12 h-12 {{ $netProfit >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                    <i class="fas {{ $netProfit >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }} text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit & Loss Statement -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-file-invoice text-blue-600 mr-2"></i> Profit & Loss Statement
                <span class="text-sm text-gray-500 ml-2">({{ date('d-m-Y', strtotime($fromDate)) }} - {{ date('d-m-Y', strtotime($toDate)) }})</span>
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Left Column: Income -->
                <div>
                    <h4 class="text-md font-semibold text-gray-700 border-b border-gray-200 pb-2 mb-3">
                        <i class="fas fa-arrow-up text-green-600 mr-2"></i> Income
                    </h4>

                    <div class="space-y-2">
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Sales Revenue</span>
                            <span class="font-medium">Rs. {{ number_format($salesRevenue, 2) }}</span>
                        </div>
                        @if($otherIncome > 0)
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Other Income</span>
                            <span class="font-medium">Rs. {{ number_format($otherIncome, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between py-2 border-t border-gray-200 font-bold">
                            <span>Total Income</span>
                            <span class="text-green-600">Rs. {{ number_format($totalIncome, 2) }}</span>
                        </div>
                    </div>

                    @if($incomeByCategory->count() > 0)
                    <div class="mt-4">
                        <h5 class="text-sm font-medium text-gray-600 mb-2">Income by Category</h5>
                        @foreach($incomeByCategory as $item)
                        <div class="flex justify-between py-1 text-sm">
                            <span class="text-gray-500">{{ $item->category->name ?? 'Uncategorized' }}</span>
                            <span>Rs. {{ number_format($item->total, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Right Column: Expenses -->
                <div>
                    <h4 class="text-md font-semibold text-gray-700 border-b border-gray-200 pb-2 mb-3">
                        <i class="fas fa-arrow-down text-red-600 mr-2"></i> Expenses
                    </h4>

                    <div class="space-y-2">
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Cost of Goods Sold</span>
                            <span class="font-medium text-orange-600">Rs. {{ number_format($cogs, 2) }}</span>
                        </div>
                        @if($operatingExpenses > 0)
                        <div class="flex justify-between py-1">
                            <span class="text-gray-600">Operating Expenses</span>
                            <span class="font-medium text-red-600">Rs. {{ number_format($operatingExpenses, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between py-2 border-t border-gray-200 font-bold">
                            <span>Total Expenses</span>
                            <span class="text-red-600">Rs. {{ number_format($cogs + $operatingExpenses, 2) }}</span>
                        </div>
                    </div>

                    @if($expensesByCategory->count() > 0)
                    <div class="mt-4">
                        <h5 class="text-sm font-medium text-gray-600 mb-2">Expenses by Category</h5>
                        @foreach($expensesByCategory as $item)
                        <div class="flex justify-between py-1 text-sm">
                            <span class="text-gray-500">{{ $item->category->name ?? 'Uncategorized' }}</span>
                            <span class="text-red-600">Rs. {{ number_format($item->total, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Net Profit / Loss Summary -->
            <div class="mt-6 p-4 rounded-lg {{ $netProfit >= 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-bold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
                    </span>
                    <span class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format(abs($netProfit), 2) }}
                    </span>
                </div>
                <div class="mt-1 text-sm {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas {{ $netProfit >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                    {{ $netProfit >= 0 ? 'Profit' : 'Loss' }} for the period
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown Chart (if data exists) -->
    @if(count($monthlyData) > 0)
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-chart-bar text-blue-600 mr-2"></i> Monthly Breakdown
            </h3>
        </div>
        <div class="p-6">
            <div style="height: 300px; position: relative;">
                <canvas id="monthlyProfitChart"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(count($monthlyData) > 0)
        var ctx = document.getElementById('monthlyProfitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json(array_column($monthlyData, 'month')),
                datasets: [{
                        label: 'Income',
                        data: @json(array_column($monthlyData, 'income')),
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2,
                        borderRadius: 4,
                    },
                    {
                        label: 'Expenses',
                        data: @json(array_column($monthlyData, 'expenses')),
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 2,
                        borderRadius: 4,
                    },
                    {
                        label: 'Profit',
                        data: @json(array_column($monthlyData, 'profit')),
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
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
        @endif
    });
</script>
@endpush
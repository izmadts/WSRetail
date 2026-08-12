@extends('layouts.admin')

@section('title', 'Trial Balance Report')
@section('page-title', 'Trial Balance')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.reports.trial-balance-pdf', request()->all()) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200">
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
                <a href="{{ route('admin.reports.trial-balance') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
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
                    <p class="text-sm font-medium text-gray-500">Total Accounts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ count($trialBalance) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Debit</p>
                    <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($totalDebitBalance, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-arrow-right text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Credit</p>
                    <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($totalCreditBalance, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-arrow-left text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Difference</p>
                    @php
                    $difference = $totalDebitBalance - $totalCreditBalance;
                    @endphp
                    <p class="text-2xl font-bold {{ $difference == 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rs. {{ number_format(abs($difference), 2) }}
                    </p>
                </div>
                <div class="w-12 h-12 {{ $difference == 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                    <i class="fas {{ $difference == 0 ? 'fa-check-circle text-green-600' : 'fa-exclamation-circle text-red-600' }} text-xl"></i>
                </div>
            </div>
            @if($difference == 0)
            <p class="text-xs text-green-600 mt-1">✓ Balanced</p>
            @else
            <p class="text-xs text-red-600 mt-1">⚠️ Not Balanced</p>
            @endif
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-balance-scale text-blue-600 mr-2"></i> Trial Balance
                <span class="text-sm text-gray-500 ml-2">({{ date('d-m-Y', strtotime($fromDate)) }} - {{ date('d-m-Y', strtotime($toDate)) }})</span>
            </h3>
        </div>

        <div class="p-6">
            @if(count($trialBalance) > 0)
            <div class="overflow-x-auto">
                <table class="w-full" id="trialBalanceTable">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Account Code</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Account Name</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Type</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Debit</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Credit</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Normal Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($trialBalance as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $item['code'] }}</code>
                            </td>
                            <td class="py-3 px-2">
                                <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                            </td>
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $item['type'] == 'Asset' ? 'bg-blue-100 text-blue-800' : 
                                           ($item['type'] == 'Liability' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($item['type'] == 'Equity' ? 'bg-purple-100 text-purple-800' : 
                                           ($item['type'] == 'Revenue' ? 'bg-green-100 text-green-800' : 
                                           'bg-red-100 text-red-800'))) }}">
                                    {{ $item['type'] }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-right font-mono">
                                @if($item['balance_type'] == 'debit')
                                <span class="text-red-600 font-semibold">Rs. {{ number_format($item['balance'], 2) }}</span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-right font-mono">
                                @if($item['balance_type'] == 'credit')
                                <span class="text-green-600 font-semibold">Rs. {{ number_format($item['balance'], 2) }}</span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $item['normal_balance'] == 'Debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $item['normal_balance'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                            <td colspan="3" class="text-right py-3 px-2 text-gray-700">Total:</td>
                            <td class="text-right py-3 px-2 text-red-600 text-lg">
                                Rs. {{ number_format($totalDebitBalance, 2) }}
                            </td>
                            <td class="text-right py-3 px-2 text-green-600 text-lg">
                                Rs. {{ number_format($totalCreditBalance, 2) }}
                            </td>
                            <td class="text-center py-3 px-2">
                                @if($totalDebitBalance == $totalCreditBalance)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Balanced
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i> Unbalanced
                                </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-database text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500">No transactions found for the selected period.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Grouped by Account Type -->
    @if(count($groupedByType) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-layer-group text-gray-400 mr-2"></i> Summary by Account Type
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($groupedByType as $type => $data)
                    <div class="border-b border-gray-100 pb-2 last:border-0">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-700">{{ $type }}</span>
                            <span class="text-sm text-gray-500">{{ $data['count'] }} accounts</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-red-600">Debit: Rs. {{ number_format($data['debit'], 2) }}</span>
                            <span class="text-green-600">Credit: Rs. {{ number_format($data['credit'], 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Account Type Pie Chart -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-pie text-gray-400 mr-2"></i> Account Distribution
                </h4>
            </div>
            <div class="p-6" style="height: 300px;">
                <canvas id="accountTypeChart"></canvas>
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
        // Account Type Chart
        var groupedData = {
            !!json_encode($groupedByType) !!
        };

        if (document.getElementById('accountTypeChart') && groupedData) {
            var labels = Object.keys(groupedData);
            var counts = labels.map(function(key) {
                return groupedData[key]['count'];
            });

            var colors = {
                'Asset': 'rgba(59, 130, 246, 0.8)',
                'Liability': 'rgba(234, 179, 8, 0.8)',
                'Equity': 'rgba(139, 92, 246, 0.8)',
                'Revenue': 'rgba(34, 197, 94, 0.8)',
                'Expense': 'rgba(239, 68, 68, 0.8)',
            };

            var backgroundColors = labels.map(function(label) {
                return colors[label] || 'rgba(156, 163, 175, 0.8)';
            });

            new Chart(document.getElementById('accountTypeChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: backgroundColors,
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
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        // DataTable
        if (document.getElementById('trialBalanceTable')) {
            new DataTable('#trialBalanceTable', {
                pageLength: 50,
                responsive: true,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        }
    });
</script>
@endpush
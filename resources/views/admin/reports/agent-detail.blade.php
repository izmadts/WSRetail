@extends('layouts.admin')

@section('title', 'Agent Details')
@section('page-title', 'Agent: ' . $user->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Agent Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-tie text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->code ?? 'AGT-' . $user->id }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $user->email }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-medium">{{ $user->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">City</span><span class="font-medium">{{ $user->city ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Commission Rate</span><span class="font-medium">{{ $user->commission_rate_cash ?? 0 }}%</span></div>
                </div>
            </div>
        </div>
        
        <!-- Summary -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b"><h4 class="font-semibold">Performance Summary</h4></div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Customers</span><span class="font-bold">{{ $totalCustomers }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Sales</span><span class="font-bold text-blue-600">Rs. {{ number_format($totalSales, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Commission</span><span class="font-bold text-purple-600">Rs. {{ number_format($totalCommission, 2) }}</span></div>
                <div class="flex justify-between border-t pt-3"><span class="text-sm font-semibold">Avg Commission</span><span class="font-bold">{{ $totalSales > 0 ? number_format(($totalCommission / $totalSales) * 100, 2) : 0 }}%</span></div>
            </div>
        </div>
        
        <a href="{{ route('admin.reports.agents') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-1"></i> Back to Agents
        </a>
    </div>

    <!-- Sales & Commission -->
    <div class="lg:col-span-2">
        <!-- Sales -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-shopping-cart text-gray-400 mr-2"></i> Recent Sales
                    <span class="text-sm text-gray-500 ml-2">({{ $user->sales->count() }} transactions)</span>
                </h4>
            </div>
            <div class="p-6">
                @if($user->sales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-2">Invoice</th>
                                <th class="text-left py-2 px-2">Customer</th>
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-right py-2 px-2">Amount</th>
                                <th class="text-right py-2 px-2">Commission</th>
                                <th class="text-center py-2 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->sales as $sale)
                            <tr>
                                <td class="py-2 px-2"><a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline">{{ $sale->invoice_no }}</a></td>
                                <td class="py-2 px-2">{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="py-2 px-2 text-right text-purple-600">Rs. {{ number_format($sale->commission_amount, 2) }}</td>
                                <td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No sales recorded yet.</p>
                @endif
            </div>
        </div>
        
        <!-- Commission Logs -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-coins text-gray-400 mr-2"></i> Commission Logs
                </h4>
            </div>
            <div class="p-6">
                @if($user->commissionLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-left py-2 px-2">Type</th>
                                <th class="text-right py-2 px-2">Amount</th>
                                <th class="text-center py-2 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->commissionLogs as $log)
                            <tr>
                                <td class="py-2 px-2">{{ $log->created_at->format('d-m-Y H:i') }}</td>
                                <td class="py-2 px-2">{{ $log->type_label }}</td>
                                <td class="py-2 px-2 text-right text-purple-600">+ Rs. {{ number_format($log->amount, 2) }}</td>
                                <td class="py-2 px-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $log->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $log->is_paid ? 'Paid' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No commission logs yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Agent Details')
@section('page-title', 'Agent: ' . $agent->name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $agent->name }}</h3>
                <p class="text-sm text-gray-500">{{ $agent->code }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $agent->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $agent->email ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-medium">{{ $agent->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Mobile</span><span class="font-medium">{{ $agent->mobile ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">City</span><span class="font-medium">{{ $agent->city ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">CNIC</span><span class="font-medium">{{ $agent->cnic ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">NTN</span><span class="font-medium">{{ $agent->ntn ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Credit Limit</span><span class="font-medium">{{ $agent->formatted_credit_limit }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Credit Days</span><span class="font-medium">{{ $agent->credit_days ?: '-' }}</span></div>
                </div>
                @if($agent->address)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Address</p>
                    <p class="text-sm text-gray-900">{{ $agent->address }}</p>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex flex-wrap gap-2">
                <a href="{{ route('admin.agents.edit', $agent) }}" class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700">Edit</a>
                <a href="{{ route('admin.agents.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300">Back</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900">Financial Summary</h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between"><span class="text-sm text-gray-500">Opening Balance</span><span class="font-medium">{{ $agent->formatted_opening_balance }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Sales</span><span class="font-medium">Rs. {{ number_format($agent->total_sales, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Paid</span><span class="font-medium text-green-600">Rs. {{ number_format($agent->total_paid, 2) }}</span></div>
                <div class="flex justify-between border-t border-gray-200 pt-3"><span class="text-sm font-semibold">Current Balance</span><span class="font-bold {{ $agent->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $agent->formatted_balance }}</span></div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900"><i class="fas fa-shopping-cart text-gray-400 mr-2"></i> Recent Sales</h4>
            </div>
            <div class="p-6">
                @if($agent->sales->count() > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2">Invoice</th>
                            <th class="text-left py-2 px-2">Date</th>
                            <th class="text-right py-2 px-2">Amount</th>
                            <th class="text-right py-2 px-2">Paid</th>
                            <th class="text-right py-2 px-2">Due</th>
                            <th class="text-center py-2 px-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($agent->sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-2"><a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline">{{ $sale->invoice_no }}</a></td>
                            <td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-red-600">Rs. {{ number_format($sale->due_amount, 2) }}</td>
                            <td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else <p class="text-center text-gray-500 py-4">No sales recorded yet.</p> @endif
            </div>
        </div>
    </div>
</div>
@endsection
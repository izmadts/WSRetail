@extends('layouts.admin')

@section('title', 'Agent Details')
@section('page-title', 'Agent: ' . $user->name)

@section('content')
<div class="space-y-6">

    <!-- ========================================== -->
    <!-- TOP: Agent Info Card with Photo -->
    <!-- ========================================== -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6 flex flex-col md:flex-row items-start md:items-center gap-6">
            <!-- Profile Photo -->
            <div class="flex-shrink-0">
                @if($user->personal_photo)
                <img src="{{ asset('storage/' . $user->personal_photo) }}"
                    alt="{{ $user->name }}"
                    class="w-24 h-24 rounded-full object-cover border-4 border-blue-100">
                @else
                <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                @endif
            </div>

            <!-- Basic Info -->
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status_color }}">
                        {{ $user->status_label }}
                    </span>
                    @if($user->is_active && $user->approved_at)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i> Approved
                    </span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        <i class="fas fa-shopping-basket mr-1"></i>
                        {{ match($user->channel) { 'wholesale' => 'Wholesale only', 'retail' => 'Retail only', default => 'Wholesale + Retail' } }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 mt-2 text-sm">
                    <div>
                        <span class="text-gray-500">Email:</span>
                        <span class="font-medium text-gray-900">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Phone:</span>
                        <span class="font-medium text-gray-900">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">CNIC:</span>
                        <span class="font-medium text-gray-900">{{ $user->cnic ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">City:</span>
                        <span class="font-medium text-gray-900">{{ $user->city ?? '-' }}</span>
                    </div>
                </div>
                @if($user->address)
                <div class="mt-1 text-sm">
                    <span class="text-gray-500">Address:</span>
                    <span class="font-medium text-gray-900">{{ $user->address }}</span>
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex-shrink-0 flex flex-wrap gap-2">
                <a href="{{ route('admin.agents.edit', $user) }}"
                    class="px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('admin.agents.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- GRID: Documents & Financial -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: Documents -->
        <div class="lg:col-span-1 space-y-6">
            <!-- CNIC Images -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-id-card text-gray-400 mr-2"></i> CNIC Documents
                    </h4>
                </div>
                <div class="p-4 space-y-3">
                    <!-- CNIC Front -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">CNIC Front</p>
                        @if($user->cnic_front_image)
                        <a href="{{ asset('storage/' . $user->cnic_front_image) }}" target="_blank"
                            class="block border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                            <img src="{{ asset('storage/' . $user->cnic_front_image) }}"
                                alt="CNIC Front"
                                class="w-full h-32 object-cover">
                        </a>
                        @else
                        <div class="border border-gray-200 rounded-lg p-4 text-center text-gray-400">
                            <i class="fas fa-image text-2xl mb-1 block"></i>
                            <span class="text-sm">No image uploaded</span>
                        </div>
                        @endif
                    </div>

                    <!-- CNIC Back -->
                    <div>
                        <p class="text-xs text-gray-500 mb-1">CNIC Back</p>
                        @if($user->cnic_back_image)
                        <a href="{{ asset('storage/' . $user->cnic_back_image) }}" target="_blank"
                            class="block border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                            <img src="{{ asset('storage/' . $user->cnic_back_image) }}"
                                alt="CNIC Back"
                                class="w-full h-32 object-cover">
                        </a>
                        @else
                        <div class="border border-gray-200 rounded-lg p-4 text-center text-gray-400">
                            <i class="fas fa-image text-2xl mb-1 block"></i>
                            <span class="text-sm">No image uploaded</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Personal Photo -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-user-circle text-gray-400 mr-2"></i> Profile Photo
                    </h4>
                </div>
                <div class="p-4 text-center">
                    @if($user->personal_photo)
                    <a href="{{ asset('storage/' . $user->personal_photo) }}" target="_blank">
                        <img src="{{ asset('storage/' . $user->personal_photo) }}"
                            alt="{{ $user->name }}"
                            class="w-40 h-40 rounded-full object-cover mx-auto border-4 border-gray-200 hover:border-blue-500 transition-all duration-200">
                    </a>
                    <p class="text-xs text-gray-400 mt-2">Click to view full size</p>
                    @else
                    <div class="w-40 h-40 rounded-full bg-gradient-to-r from-gray-300 to-gray-400 flex items-center justify-center mx-auto text-white text-4xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payout Information -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-university text-gray-400 mr-2"></i> Payout Information
                    </h4>
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Account Type</span>
                        <span class="font-medium">{{ ucfirst($user->payout_account_type ?? '-') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Account Title</span>
                        <span class="font-medium">{{ $user->payout_account_title ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Account Number</span>
                        <span class="font-medium">{{ $user->payout_account_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Provider</span>
                        <span class="font-medium">{{ $user->payout_account_provider ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Financial & Commission -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-money-bill-wave text-gray-400 mr-2"></i> Financial Summary
                    </h4>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-500">Basic Salary</p>
                        <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($user->basic_salary, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-500">Fuel Allowance</p>
                        <p class="text-xl font-bold text-green-600">Rs. {{ number_format($user->fuel_allowance, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-gray-500">Credit Commission Rate</p>
                        <p class="text-xl font-bold text-purple-600">{{ $user->commission_rate_credit }}%</p>
                    </div>
                </div>
            </div>

            <!-- Commission Slabs -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-layer-group text-gray-400 mr-2"></i> Cash Sales Commission Slabs
                    </h4>
                </div>
                <div class="p-6">
                    @if($user->commission_slabs)
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-2 font-semibold text-gray-600">From (Rs.)</th>
                                <th class="text-left py-2 px-2 font-semibold text-gray-600">To (Rs.)</th>
                                <th class="text-left py-2 px-2 font-semibold text-gray-600">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->commission_slabs as $slab)
                            <tr>
                                <td class="py-2 px-2">Rs. {{ number_format($slab['from']) }}</td>
                                <td class="py-2 px-2">{{ $slab['to'] ? 'Rs. '.number_format($slab['to']) : '∞ (Unlimited)' }}</td>
                                <td class="py-2 px-2 font-medium text-green-600">{{ $slab['rate'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-gray-500 text-center py-4">
                        No slabs defined. Using single rate: <strong>{{ $user->commission_rate_cash }}%</strong>
                    </p>
                    @endif
                </div>
            </div>

            <!-- Commission Summary -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-coins text-gray-400 mr-2"></i> Commission Summary
                    </h4>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-500">Total Earned</p>
                        <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($totalCommission, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-500">Paid</p>
                        <p class="text-xl font-bold text-green-600">Rs. {{ number_format($paidCommission, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <p class="text-sm text-gray-500">Due</p>
                        <p class="text-xl font-bold text-red-600">Rs. {{ number_format($dueCommission, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h4 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-shopping-cart text-gray-400 mr-2"></i> Recent Sales
                    </h4>
                    <span class="text-sm text-gray-500">Total: {{ $user->sales->count() }}</span>
                </div>
                <div class="p-6">
                    @if($user->sales->count() > 0)
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-2">Invoice</th>
                                <th class="text-left py-2 px-2">Customer</th>
                                <th class="text-left py-2 px-2">Date</th>
                                <th class="text-right py-2 px-2">Amount</th>
                                <th class="text-right py-2 px-2">Commission</th>
                                <th class="text-center py-2 px-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($user->sales->take(10) as $sale)
                            <tr>
                                <td class="py-2 px-2 font-medium">{{ $sale->invoice_no }}</td>
                                <td class="py-2 px-2">{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="py-2 px-2 text-right text-purple-600">Rs. {{ number_format($sale->commission_amount, 2) }}</td>
                                <td class="py-2 px-2 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">
                                        {{ $sale->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($user->sales->count() > 10)
                    <div class="mt-3 text-center">
                        <a href="#" class="text-sm text-blue-600 hover:underline">View all sales</a>
                    </div>
                    @endif
                    @else
                    <p class="text-center text-gray-500 py-4">No sales yet.</p>
                    @endif
                </div>
            </div>

            <!-- Admin Note -->
            @if($user->admin_note)
            <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4">
                <p class="text-sm font-medium text-yellow-800">
                    <i class="fas fa-sticky-note mr-2"></i> Admin Note
                </p>
                <p class="text-sm text-yellow-700 mt-1">{{ $user->admin_note }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
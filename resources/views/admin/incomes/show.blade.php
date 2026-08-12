@extends('layouts.admin')

@section('title', 'Income Details')
@section('page-title', 'Income: ' . $income->income_no)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-gray-400 mr-2"></i> Income Info
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Income No</span>
                    <span class="font-medium">{{ $income->income_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Title</span>
                    <span class="font-medium">{{ $income->title }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Category</span>
                    <span class="font-medium">{{ $income->category->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Amount</span>
                    <span class="font-bold text-green-600 text-lg">Rs. {{ number_format($income->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Date</span>
                    <span class="font-medium">{{ $income->income_date->format('d-m-Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Payment Method</span>
                    <span class="font-medium">{{ $income->payment_method_label }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Source</span>
                    <span class="font-medium">{{ $income->source_label }}</span>
                </div>
                @if($income->reference_no)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Reference No</span>
                    <span class="font-medium">{{ $income->reference_no }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Created By</span>
                    <span class="font-medium">{{ $income->createdBy->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        @if($income->receipt)
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-file-alt text-gray-400 mr-2"></i> Receipt
                </h4>
            </div>
            <div class="p-6 text-center">
                <a href="{{ asset('storage/' . $income->receipt) }}" target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-eye mr-2"></i> View Receipt
                </a>
            </div>
        </div>
        @endif

        <div class="flex gap-2">
            <a href="{{ route('admin.incomes.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <a href="{{ route('admin.incomes.edit', $income) }}" class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
        </div>
    </div>

    <!-- Right Column -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-file-invoice text-gray-400 mr-2"></i> Description & Notes
                </h4>
            </div>
            <div class="p-6 space-y-4">
                @if($income->description)
                <div>
                    <p class="text-sm font-medium text-gray-700">Description</p>
                    <p class="text-gray-900 mt-1">{{ $income->description }}</p>
                </div>
                @endif
                @if($income->notes)
                <div>
                    <p class="text-sm font-medium text-gray-700">Notes</p>
                    <p class="text-gray-900 mt-1">{{ $income->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
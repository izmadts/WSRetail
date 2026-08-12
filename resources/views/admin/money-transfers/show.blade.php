@extends('layouts.admin')

@section('title', 'Transfer Details')
@section('page-title', 'Transfer: ' . $moneyTransfer->transfer_no)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-gray-400 mr-2"></i> Transfer Info
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Transfer No</span>
                    <span class="font-medium">{{ $moneyTransfer->transfer_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">From</span>
                    <span class="font-medium">{{ $moneyTransfer->fromAccount->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">To</span>
                    <span class="font-medium">{{ $moneyTransfer->toAccount->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Amount</span>
                    <span class="font-bold text-blue-600 text-lg">Rs. {{ number_format($moneyTransfer->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Date</span>
                    <span class="font-medium">{{ $moneyTransfer->transfer_date->format('d-m-Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $moneyTransfer->status_color }}">
                        {{ $moneyTransfer->status_label }}
                    </span>
                </div>
                @if($moneyTransfer->reference_no)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Reference No</span>
                    <span class="font-medium">{{ $moneyTransfer->reference_no }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Created By</span>
                    <span class="font-medium">{{ $moneyTransfer->createdBy->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.money-transfers.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            @if($moneyTransfer->status == 'pending')
            <a href="{{ route('admin.money-transfers.edit', $moneyTransfer) }}" class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            @endif
        </div>
    </div>

    <!-- Right Column -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-file-invoice text-gray-400 mr-2"></i> Description
                </h4>
            </div>
            <div class="p-6">
                @if($moneyTransfer->description)
                <p class="text-gray-900">{{ $moneyTransfer->description }}</p>
                @else
                <p class="text-gray-500">No description provided.</p>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        @if($moneyTransfer->status == 'pending')
        <div class="mt-6 bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-tasks text-gray-400 mr-2"></i> Actions
                </h4>
            </div>
            <div class="p-6 flex flex-wrap gap-3">
                <form action="{{ route('admin.money-transfers.complete', $moneyTransfer) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-check mr-1"></i> Complete Transfer
                    </button>
                </form>
                <form action="{{ route('admin.money-transfers.cancel', $moneyTransfer) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Cancel this transfer?')" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </form>
                <form action="{{ route('admin.money-transfers.destroy', $moneyTransfer) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this transfer?')" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
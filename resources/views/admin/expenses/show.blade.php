@extends('layouts.admin')

@section('title', 'Expense Details')
@section('page-title', 'Expense: ' . $expense->expense_no)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-gray-400 mr-2"></i> Expense Info
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Expense No</span>
                    <span class="font-medium">{{ $expense->expense_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Title</span>
                    <span class="font-medium">{{ $expense->title }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Category</span>
                    <span class="font-medium">{{ $expense->category->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Amount</span>
                    <span class="font-bold text-red-600 text-lg">Rs. {{ number_format($expense->amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Date</span>
                    <span class="font-medium">{{ $expense->expense_date->format('d-m-Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Payment Method</span>
                    <span class="font-medium">{{ $expense->payment_method_label }}</span>
                </div>
                @if($expense->reference_no)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Reference No</span>
                    <span class="font-medium">{{ $expense->reference_no }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $expense->status_color }}">
                        {{ $expense->status_label }}
                    </span>
                </div>
                @if($expense->approved_by)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Approved By</span>
                    <span class="font-medium">{{ $expense->approvedBy->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Approved At</span>
                    <span class="font-medium">{{ $expense->approved_at ? $expense->approved_at->format('d-m-Y H:i') : '-' }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Created By</span>
                    <span class="font-medium">{{ $expense->createdBy->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        @if($expense->receipt)
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-file-alt text-gray-400 mr-2"></i> Receipt
                </h4>
            </div>
            <div class="p-6 text-center">
                <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-eye mr-2"></i> View Receipt
                </a>
            </div>
        </div>
        @endif

        <div class="flex gap-2">
            <a href="{{ route('admin.expenses.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            @if(!in_array($expense->status, ['paid', 'cancelled']))
            <a href="{{ route('admin.expenses.edit', $expense) }}" class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
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
                    <i class="fas fa-file-invoice text-gray-400 mr-2"></i> Description & Notes
                </h4>
            </div>
            <div class="p-6 space-y-4">
                @if($expense->description)
                <div>
                    <p class="text-sm font-medium text-gray-700">Description</p>
                    <p class="text-gray-900 mt-1">{{ $expense->description }}</p>
                </div>
                @endif
                @if($expense->notes)
                <div>
                    <p class="text-sm font-medium text-gray-700">Notes</p>
                    <p class="text-gray-900 mt-1">{{ $expense->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-tasks text-gray-400 mr-2"></i> Actions
                </h4>
            </div>
            <div class="p-6 flex flex-wrap gap-3">
                @if($expense->status == 'pending')
                <form action="{{ route('admin.expenses.approve', $expense) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                </form>
                @endif
                @if($expense->status == 'approved')
                <form action="{{ route('admin.expenses.mark-paid', $expense) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-check-double mr-1"></i> Mark as Paid
                    </button>
                </form>
                @endif
                @if(!in_array($expense->status, ['paid', 'cancelled']))
                <form action="{{ route('admin.expenses.cancel', $expense) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Cancel this expense?')" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </form>
                @endif
                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete this expense?')" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
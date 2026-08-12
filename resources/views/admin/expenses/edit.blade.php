@extends('layouts.admin')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense: ' . $expense->expense_no)

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Expense
        </h3>
    </div>

    <div class="p-4 sm:p-6"
        x-data="{
            method: '{{ old('payment_method', $expense->payment_method) }}',
            amount: {{ (float) old('amount', $expense->amount) }},
            cashBalance: {{ (float) $cashBalance }},
            bankBalance: {{ (float) $bankBalance }},
            // This expense's own amount may already be posted to the ledger
            // (if it's currently approved/paid) - add it back for the
            // account it was originally posted to, so re-submitting it
            // unchanged doesn't falsely warn about cash it already used.
            originalMethod: '{{ $expense->payment_method }}',
            originalAmount: {{ (float) $expense->amount }},
            alreadyPosted: {{ in_array($expense->status, ['approved', 'paid']) ? 'true' : 'false' }},
            get available() {
                const base = this.method === 'cash' ? this.cashBalance : this.bankBalance;
                return (this.alreadyPosted && this.method === this.originalMethod) ? base + this.originalAmount : base;
            },
            get short() { return (parseFloat(this.amount) || 0) > this.available },
        }">
        <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data"
            @submit="if (short && !confirm('This account only has Rs. ' + available.toFixed(2) + ' available - saving this expense will take it negative. Submit anyway?')) $event.preventDefault()">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $expense->title) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category
                        <x-help-tooltip>Groups this expense for reporting/filtering only - every expense posts to the same General Expenses ledger account regardless of category.</x-help-tooltip>
                    </label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" x-model="amount" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           min="0.01" step="0.01">
                    <p class="text-xs mt-1" :class="short ? 'text-orange-600 font-medium' : 'text-gray-400'">
                        Available in <span x-text="method === 'cash' ? 'Cash' : 'Bank'"></span>: Rs. <span x-text="available.toFixed(2)"></span>
                        <template x-if="short"> - insufficient, will go negative</template>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Method <span class="text-red-500">*</span>
                        <x-help-tooltip>Decides which account is credited when this posts to the ledger - Cash credits the Cash account, anything else (Bank Transfer/Cheque/Credit Card) credits the Bank account.</x-help-tooltip>
                    </label>
                    <select name="payment_method" x-model="method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method', $expense->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="credit_card" {{ old('payment_method', $expense->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $expense->reference_no) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           placeholder="Cheque/Transaction ID">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                        <x-help-tooltip>Only Approved or Paid actually post this to the ledger - Pending and Cancelled have zero accounting effect. Changing status here reverses and re-posts the ledger entries to match.</x-help-tooltip>
                    </label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="pending" {{ old('status', $expense->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status', $expense->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ old('status', $expense->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ old('status', $expense->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
                    @if($expense->receipt)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank" class="text-blue-600 hover:underline">
                                View Current Receipt
                            </a>
                        </div>
                    @endif
                    <input type="file" name="receipt" accept="image/*,.pdf"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('description', $expense->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Update Expense
                </button>
                <a href="{{ route('admin.expenses.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
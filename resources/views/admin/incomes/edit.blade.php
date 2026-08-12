@extends('layouts.admin')

@section('title', 'Edit Income')
@section('page-title', 'Edit Income: ' . $income->income_no)

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Income
        </h3>
    </div>

    <div class="p-4 sm:p-6">
        <form action="{{ route('admin.incomes.update', $income) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $income->title) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category
                        <x-help-tooltip>Groups this income for reporting/filtering only - which ledger account gets credited is decided entirely by Source, not by category.</x-help-tooltip>
                    </label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $income->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $income->amount) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           min="0.01" step="0.01">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Income Date <span class="text-red-500">*</span></label>
                    <input type="date" name="income_date" value="{{ old('income_date', $income->income_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Method <span class="text-red-500">*</span>
                        <x-help-tooltip>Decides which account is debited when this posts to the ledger - Cash debits the Cash account, anything else (Bank Transfer/Cheque/Credit Card) debits the Bank account. Editing this reverses and re-posts the ledger entries to match.</x-help-tooltip>
                    </label>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="cash" {{ old('payment_method', $income->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $income->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method', $income->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="credit_card" {{ old('payment_method', $income->payment_method) == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source <span class="text-red-500">*</span></label>
                    <select name="source" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="sale" {{ old('source', $income->source) == 'sale' ? 'selected' : '' }}>Sale</option>
                        <option value="investment" {{ old('source', $income->source) == 'investment' ? 'selected' : '' }}>Investment</option>
                        <option value="loan" {{ old('source', $income->source) == 'loan' ? 'selected' : '' }}>Loan</option>
                        <option value="other" {{ old('source', $income->source) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <p class="mt-1 text-xs text-amber-600"><i class="fas fa-exclamation-triangle mr-1"></i>Only use "Sale" here for revenue that was NOT already recorded through the Sales module - otherwise it will double-count that revenue.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $income->reference_no) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           placeholder="Cheque/Transaction ID">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
                    @if($income->receipt)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $income->receipt) }}" target="_blank" class="text-blue-600 hover:underline">
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
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('description', $income->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('notes', $income->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Update Income
                </button>
                <a href="{{ route('admin.incomes.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
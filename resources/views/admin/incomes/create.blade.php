@extends('layouts.admin')

@section('title', 'Add Income')
@section('page-title', 'Create New Income')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            <i class="fas fa-plus-circle text-green-600 mr-2"></i> New Income
        </h3>
    </div>

    <div class="p-4 sm:p-6">
        <form action="{{ route('admin.incomes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category
                        <x-help-tooltip>Groups this income for reporting/filtering only - which ledger account gets credited is decided entirely by Source (below), not by category.</x-help-tooltip>
                    </label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', 0) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           min="0.01" step="0.01">
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Income Date <span class="text-red-500">*</span></label>
                    <input type="date" name="income_date" value="{{ old('income_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    @error('income_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Method <span class="text-red-500">*</span>
                        <x-help-tooltip>Decides which account is debited when this posts to the ledger - Cash debits the Cash account, anything else (Bank Transfer/Cheque/Credit Card) debits the Bank account. Posts immediately on save; there's no draft/pending state for income.</x-help-tooltip>
                    </label>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    </select>
                    @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source <span class="text-red-500">*</span></label>
                    <select name="source" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="sale" {{ old('source') == 'sale' ? 'selected' : '' }}>Sale</option>
                        <option value="investment" {{ old('source') == 'investment' ? 'selected' : '' }}>Investment</option>
                        <option value="loan" {{ old('source') == 'loan' ? 'selected' : '' }}>Loan</option>
                        <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('source')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-amber-600"><i class="fas fa-exclamation-triangle mr-1"></i>Only use "Sale" here for revenue that was NOT already recorded through the Sales module - otherwise it will double-count that revenue.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="Cheque/Transaction ID">
                    @error('reference_no')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt</label>
                    <input type="file" name="receipt" accept="image/*,.pdf"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    @error('receipt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Record Income
                </button>
                <a href="{{ route('admin.incomes.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
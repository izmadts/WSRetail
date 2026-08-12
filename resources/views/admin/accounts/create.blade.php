@extends('layouts.admin')

@section('title', 'Add Account')
@section('page-title', 'Create New Account')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-plus-circle text-blue-600 mr-2"></i> New Account
        </h3>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.accounts.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Account Code <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-500 @enderror"
                        id="code" name="code" value="{{ old('code') }}" required>
                    @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Account Name <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                        id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Account Type <span class="text-red-500">*</span></label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror"
                        id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Asset" {{ old('type') == 'Asset' ? 'selected' : '' }}>Asset</option>
                        <option value="Liability" {{ old('type') == 'Liability' ? 'selected' : '' }}>Liability</option>
                        <option value="Equity" {{ old('type') == 'Equity' ? 'selected' : '' }}>Equity</option>
                        <option value="Revenue" {{ old('type') == 'Revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="Expense" {{ old('type') == 'Expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="normal_balance" class="block text-sm font-medium text-gray-700 mb-1">Normal Balance <span class="text-red-500">*</span></label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('normal_balance') border-red-500 @enderror"
                        id="normal_balance" name="normal_balance" required>
                        <option value="">Select Balance</option>
                        <option value="Debit" {{ old('normal_balance') == 'Debit' ? 'selected' : '' }}>Debit</option>
                        <option value="Credit" {{ old('normal_balance') == 'Credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                    @error('normal_balance')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Account</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('parent_id') border-red-500 @enderror"
                        id="parent_id" name="parent_id">
                        <option value="">None</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->code }} - {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Create Account
                </button>
                <a href="{{ route('admin.accounts.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Add User')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-user-plus text-blue-600 mr-2"></i> Add User</h4>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.settings.users.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="accountant" {{ old('role') == 'accountant' ? 'selected' : '' }}>Accountant</option>
                        </select>
                        @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <div x-data="{ showPassword: false }" class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required minlength="8"
                                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <div x-data="{ showPassword: false }" class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i> Create User
                    </button>
                    <a href="{{ route('admin.settings.users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

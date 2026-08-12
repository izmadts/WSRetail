@extends('layouts.agent')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-user-edit text-green-600 mr-2"></i> Edit Profile
        </h3>
    </div>

    <div class="p-6">
        <form action="{{ route('agent.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $agent->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $agent->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $agent->whatsapp_number) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $agent->city) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('address', $agent->address) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <hr>
                    <p class="text-sm font-medium text-gray-700 mb-2">Change Password (leave blank to keep)</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div x-data="{ showPassword: false }" class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password"
                                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-green-600">
                                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <div x-data="{ showPassword: false }" class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password_confirmation"
                                       class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-green-600">
                                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">
                    <i class="fas fa-save mr-1"></i> Update Profile
                </button>
                <a href="{{ route('agent.dashboard') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
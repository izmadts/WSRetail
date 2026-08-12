<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="w-full max-w-7xl mx-auto">

            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg">
                    <span class="text-white text-3xl font-bold">W</span>
                </div>
                <h2 class="mt-3 text-3xl font-extrabold text-gray-900">Sales Agent Registration</h2>
                <p class="mt-1 text-sm text-gray-600">Join our team and start earning commission</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <form method="POST" action="{{ route('agent.register.post') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- ========================================== -->
                    <!-- 1. PERSONAL INFORMATION - 3 Columns -->
                    <!-- ========================================== -->
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guardian Name</label>
                                <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CNIC <span class="text-red-500">*</span></label>
                                <input type="text" name="cnic" value="{{ old('cnic') }}" required placeholder="12345-1234567-8"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('cnic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                                <textarea name="address" rows="1" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                <input type="text" name="city" value="{{ old('city') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
                                <input type="text" name="country" value="{{ old('country') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('country')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- 2. DOCUMENTS - 3 Columns -->
                    <!-- ========================================== -->
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CNIC Front <span class="text-red-500">*</span></label>
                                <input type="file" name="cnic_front_image" accept="image/*" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('cnic_front_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CNIC Back <span class="text-red-500">*</span></label>
                                <input type="file" name="cnic_back_image" accept="image/*" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('cnic_back_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Personal Photo <span class="text-red-500">*</span></label>
                                <input type="file" name="personal_photo" accept="image/*" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('personal_photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- 3. PAYOUT INFORMATION - 3 Columns -->
                    <!-- ========================================== -->
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payout Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Type <span class="text-red-500">*</span></label>
                                <select name="payout_account_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="bank" {{ old('payout_account_type') == 'bank' ? 'selected' : '' }}>Bank Account</option>
                                    <option value="easypaisa" {{ old('payout_account_type') == 'easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                    <option value="jazzcash" {{ old('payout_account_type') == 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                                </select>
                                @error('payout_account_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Title <span class="text-red-500">*</span></label>
                                <input type="text" name="payout_account_title" value="{{ old('payout_account_title') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('payout_account_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number <span class="text-red-500">*</span></label>
                                <input type="text" name="payout_account_number" value="{{ old('payout_account_number') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('payout_account_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provider <span class="text-red-500">*</span></label>
                                <input type="text" name="payout_account_provider" value="{{ old('payout_account_provider') }}" required placeholder="e.g., HBL, UBL"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('payout_account_provider')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- 4. REFERENCE - 3 Columns -->
                    <!-- ========================================== -->
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reference (Optional)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Name</label>
                                <input type="text" name="reference_name" value="{{ old('reference_name') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Phone</label>
                                <input type="text" name="reference_phone_number" value="{{ old('reference_phone_number') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Address</label>
                                <input type="text" name="reference_address" value="{{ old('reference_address') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- 5. PASSWORD & SUBMIT (WITH EYE TOGGLE) -->
                    <!-- ========================================== -->
                    <div x-data="{ showPassword: false, showConfirmPassword: false }" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="space-y-4">
                                <!-- Password -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                            <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="flex flex-col justify-between items-start md:items-end">
                            <div>
                                <label class="flex items-start">
                                    <input type="checkbox" name="policy_accepted" value="1" required
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                    <span class="ml-2 text-sm text-gray-600">
                                        I have read and agree to the
                                        <a href="{{ route('agent.policy') }}" target="_blank" class="text-blue-600 hover:underline">Sales Commission Policy</a>
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                @error('policy_accepted')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="mt-4 w-full flex flex-col sm:flex-row gap-3">
                                <a href="{{ route('login') }}" class="text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    Already have account? Login
                                </a>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                                    Submit Application
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
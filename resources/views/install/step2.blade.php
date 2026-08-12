<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 2 - Admin Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Admin & Company Setup</h2>
                    <span class="text-sm text-gray-500">Step 2 of 2</span>
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Create admin account and company details</p>
            </div>

            <form method="POST" action="{{ route('install.step2') }}" enctype="multipart/form-data">
                @csrf

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">Admin Account</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('admin_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('admin_email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="admin_password" name="admin_password" required
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" onclick="wserpTogglePassword('admin_password', this)" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                        @error('admin_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" required
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" onclick="wserpTogglePassword('admin_password_confirmation', this)" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <h3 class="font-semibold text-gray-700 border-b pb-2 mt-4">Company Details</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('company_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Logo</label>
                        <input type="file" name="company_logo" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Recommended: 200x200px PNG or JPG</p>
                        @error('company_logo')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Phone</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                        <textarea name="company_address" rows="2" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('company_address') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-between items-center border-t border-gray-200 pt-6">
                    <a href="{{ route('install.step1') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-check mr-2"></i> Install
                    </button>
                </div>
            </form>

            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Installation will create database tables and admin account.
                </p>
            </div>
        </div>
    </div>
    <script>
        function wserpTogglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector('i');
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        }
    </script>
</body>
</html>
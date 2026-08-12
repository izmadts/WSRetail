<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 1 - Database Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Database Setup</h2>
                    <span class="text-sm text-gray-500">Step 1 of 2</span>
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 50%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Enter your database credentials</p>
            </div>

            <form method="POST" action="{{ route('install.step1') }}">
                @csrf

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Database Host <span class="text-red-500">*</span></label>
                            <input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('db_host')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Port <span class="text-red-500">*</span></label>
                            <input type="text" name="db_port" value="{{ old('db_port', '3306') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('db_port')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name <span class="text-red-500">*</span></label>
                        <input type="text" name="db_database" value="{{ old('db_database') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('db_database')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Username <span class="text-red-500">*</span></label>
                        <input type="text" name="db_username" value="{{ old('db_username') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('db_username')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database Password</label>
                        <div class="relative">
                            <input type="password" id="db_password" name="db_password" value="{{ old('db_password') }}"
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button type="button" onclick="wserpTogglePassword('db_password', this)" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Leave empty if no password</p>
                        @error('db_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-between items-center border-t border-gray-200 pt-6">
                    <a href="{{ route('install.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                        Next Step →
                    </button>
                </div>
            </form>
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
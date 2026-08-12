<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WSERP Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto">
                    <span class="text-white text-3xl font-bold">W</span>
                </div>
                <h1 class="mt-4 text-3xl font-bold text-gray-900">WSERP Installation</h1>
                <p class="mt-2 text-gray-600">Complete Setup Wizard</p>
            </div>

            <div class="mt-8 space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">✓</div>
                    <span class="text-gray-700">PHP 8.1+ Required</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">✓</div>
                    <span class="text-gray-700">MySQL Database Required</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-bold">!</div>
                    <span class="text-gray-700">Please have your database credentials ready</span>
                </div>
            </div>

            @if(session('error'))
                <div class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-8 border-t border-gray-200 pt-6">
                <a href="{{ route('install.step1') }}" 
                   class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                    Start Installation →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
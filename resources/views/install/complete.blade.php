<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900">Installation Complete!</h1>
            <p class="mt-2 text-gray-600">WSERP has been successfully installed.</p>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-6 text-left">
                <h4 class="font-semibold text-green-800 mb-2">✓ Installation Summary</h4>
                <ul class="text-sm text-green-700 space-y-1">
                    <li>• Database tables created</li>
                    <li>• Admin account created</li>
                    <li>• Company settings saved</li>
                    <li>• Application configured</li>
                </ul>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4 text-left">
                <h4 class="font-semibold text-yellow-800 mb-2">⚠️ Important Security Steps</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Delete the storage/installed file to reinstall</li>
                    <li>• Change default admin password if used</li>
                    <li>• Enable HTTPS if not already</li>
                </ul>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-6">
                <a href="{{ route('login') }}"
                   class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login to Admin
                </a>
                <a href="{{ url('/') }}" 
                   class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i> Visit Website
                </a>
            </div>

            <p class="text-xs text-gray-400 mt-6">
                <i class="fas fa-shield-alt mr-1"></i>
                To reinstall, delete: <code class="bg-gray-100 px-2 py-0.5 rounded">storage/installed</code>
            </p>
        </div>
    </div>
</body>
</html>
<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Application Submitted!</h2>
                <p class="mt-2 text-gray-600">Your application has been received successfully.</p>
                <div class="mt-4 p-4 bg-blue-50 rounded-lg text-left">
                    <p class="text-sm text-blue-800">📌 Next Steps:</p>
                    <ul class="mt-2 text-sm text-blue-700 list-disc list-inside">
                        <li>Our team will review your application</li>
                        <li>You will receive an email notification</li>
                        <li>Once approved, you can login to your agent dashboard</li>
                    </ul>
                </div>
                <a href="{{ route('login') }}" class="mt-6 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    Go to Login
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
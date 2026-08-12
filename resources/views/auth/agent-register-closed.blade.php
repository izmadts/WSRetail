<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-gray-500 text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Registration Closed</h2>
                <p class="mt-2 text-gray-600">We're not accepting new sales agent applications right now. Please check back later, or contact the admin if you believe this is a mistake.</p>
                <a href="{{ route('login') }}" class="mt-6 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    Go to Login
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>

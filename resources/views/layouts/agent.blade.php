<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    @if($siteFavicon ?? null)<link rel="icon" href="{{ asset($siteFavicon) }}">@endif
    <title>@yield('title', 'WSERP - Agent Panel')</title>
    {{-- Runs synchronously, before anything paints, so the page never
         flashes light-then-dark on reload. See layouts/admin.blade.php for
         the matching comment. --}}
    <script>
        (function () {
            var stored = localStorage.getItem('wserp-theme');
            if (stored === 'dark') document.documentElement.classList.add('dark');
            window.wserpToggleTheme = function () {
                var isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('wserp-theme', isDark ? 'dark' : 'light');
            };
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-style')
    @livewireStyles
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full font-sans antialiased">
    <div x-data="{ sidebarOpen: true }" class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 shadow-lg transform transition-transform duration-300 ease-in-out"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                <div class="flex items-center">
                    @if($siteLogo)
                        <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" class="h-8 w-auto max-w-[7rem] object-contain">
                    @else
                        <div class="w-8 h-8 bg-gradient-to-r from-green-600 to-green-700 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ substr($siteName, 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="ml-2 text-xl font-bold text-gray-800">Agent Panel</span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="px-2 py-4 overflow-y-auto h-[calc(100vh-4rem)]">
                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Main Menu</p>
                    <a href="{{ route('agent.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-home w-5 text-lg"></i><span class="ml-3">Dashboard</span>
                    </a>
                    <a href="{{ route('agent.customers.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.customers.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-users w-5 text-lg"></i><span class="ml-3">My Customers</span>
                    </a>
                    <a href="{{ route('agent.sales.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.sales.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-shopping-cart w-5 text-lg"></i><span class="ml-3">My Sales</span>
                    </a>
                    <a href="{{ route('agent.commissions.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.commissions.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-coins w-5 text-lg"></i><span class="ml-3">Commissions</span>
                    </a>
                    <a href="{{ route('agent.reports.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.reports.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-chart-bar w-5 text-lg"></i><span class="ml-3">Reports</span>
                    </a>
                    <a href="{{ route('agent.leave.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.leave.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-calendar-day w-5 text-lg"></i><span class="ml-3">Leave</span>
                    </a>
                    <a href="{{ route('agent.payslips.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.payslips.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-file-invoice-dollar w-5 text-lg"></i><span class="ml-3">Payslips</span>
                    </a>
                    <a href="{{ route('agent.profile.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('agent.profile.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-user w-5 text-lg"></i><span class="ml-3">My Profile</span>
                    </a>
                </div>
                <hr class="my-4 border-gray-200">
                <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center text-white font-semibold text-sm">{{ auth()->user() ? substr(auth()->user()->name,0,2) : 'A' }}</div>
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user() ? auth()->user()->name : 'Guest' }}</p><p class="text-xs text-gray-500 truncate">{{ auth()->user() ? auth()->user()->email : '' }}</p></div>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-gray-400 hover:text-gray-600"><i class="fas fa-sign-out-alt"></i></button></form>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 lg:hidden"><i class="fas fa-bars text-xl"></i></button>
                        <h1 class="ml-2 text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($darkModeEnabled ?? true)
                        <button type="button" onclick="wserpToggleTheme()" title="Toggle dark mode"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:inline"></i>
                        </button>
                        @endif
                        <span class="text-xs text-gray-500 hidden sm:block">{{ auth()->user()->role ?? '' }}</span>
                    </div>
                </div>
            </header>
            <main class="p-4 lg:p-6">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                        <div class="flex items-center"><i class="fas fa-check-circle text-green-400 text-xl"></i><p class="ml-3 text-green-700">{{ session('success') }}</p><button @click="show = false" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button></div>
                    </div>
                @endif
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                        <div class="flex items-center"><i class="fas fa-exclamation-circle text-red-400 text-xl"></i><p class="ml-3 text-red-700">{{ session('error') }}</p><button @click="show = false" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button></div>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @livewireScripts
    @vite(['resources/js/app.js'])
    <script>function confirmDelete(m){return confirm(m||'Are you sure?');}</script>
    @yield('scripts')
    {{-- @push('scripts') pages (dashboard chart, DataTable init, etc.) need
    a matching @stack - it was silently discarded with no @stack anywhere
    in this layout. --}}
    @stack('scripts')
</body>
</html>
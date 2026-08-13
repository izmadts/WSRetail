<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    @if($siteFavicon ?? null)<link rel="icon" href="{{ asset($siteFavicon) }}">@endif
    <title>@yield('title', 'POS')</title>

    {{-- Same synchronous pre-paint theme script as layouts.admin - keeps
         dark mode consistent if a cashier opened POS in a new tab from an
         already-dark admin session. --}}
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-style')

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-full font-sans antialiased">
    <div class="min-h-screen flex flex-col">

        <!-- Compact POS top bar - no sidebar, no admin navigation. -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-3 sm:px-4 py-2">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    @if($siteLogo ?? null)
                        <img src="{{ asset($siteLogo) }}" alt="{{ $siteName ?? 'WSRetail' }}" class="h-7 w-auto max-w-[6rem] object-contain">
                    @else
                        <span class="font-bold text-blue-600 truncate">{{ $siteName ?? 'WSRetail' }}</span>
                    @endif
                    <span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full font-medium whitespace-nowrap">POS</span>
                    @if(auth()->user()->location)
                    <span class="hidden sm:inline text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full whitespace-nowrap">
                        <i class="fas fa-map-marker-alt mr-1"></i>{{ auth()->user()->location->name }}
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-1 sm:gap-2" x-data="{ now: new Date().toLocaleString() }" x-init="setInterval(() => now = new Date().toLocaleString(), 1000)">
                    <span class="hidden md:inline text-xs text-gray-500 mr-2" x-text="now"></span>

                    <div x-data="{ fs: false }" @fullscreenchange.window="fs = !!document.fullscreenElement" class="hidden sm:block">
                        <button type="button" title="Toggle fullscreen"
                            @click="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-expand" x-show="!fs"></i>
                            <i class="fas fa-compress" x-show="fs" x-cloak></i>
                        </button>
                    </div>

                    @if($darkModeEnabled ?? true)
                    <button type="button" onclick="wserpToggleTheme()" title="Toggle dark mode"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    @endif

                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 pl-1 focus:outline-none">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter.duration.150ms class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @unless(auth()->user()->isPosManager())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-th-large w-5 text-gray-400"></i><span class="ml-3">Dashboard</span>
                            </a>
                            <hr class="my-1 border-gray-100">
                            @endunless
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt w-5 text-red-400"></i><span class="ml-3">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-3 sm:p-4">
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-3 p-3 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                <div class="flex items-center"><i class="fas fa-check-circle text-green-400"></i>
                    <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p><button @click="show = false" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                </div>
            </div>
            @endif
            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" class="mb-3 p-3 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                <div class="flex items-center"><i class="fas fa-exclamation-circle text-red-400"></i>
                    <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p><button @click="show = false" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                </div>
            </div>
            @endif
            @yield('content')
        </main>
    </div>

    @yield('scripts')
    @stack('scripts')
</body>
</html>

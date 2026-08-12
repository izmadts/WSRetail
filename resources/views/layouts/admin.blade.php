<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    @if($siteFavicon ?? null)<link rel="icon" href="{{ asset($siteFavicon) }}">@endif
    <title>@yield('title', 'WSERP - Admin Panel')</title>

    {{-- Runs synchronously, before anything paints, so the page never
         flashes light-then-dark on reload. Deliberately plain JS, not
         Alpine (which is bundled/deferred and would run too late) -
         wserpToggleTheme() is also used by the header button below. --}}
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

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-style')

    {{-- @livewireStyles --}}

    <style>
        [x-cloak] {
            display: none !important;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .transition-fast {
            transition: all 0.15s ease-in-out;
        }

        /* Sidebar component classes (.sidebar-link, .sidebar-overlay, etc.)
           moved to resources/css/app.css's @layer components - @apply only
           compiles there, not in an inline <style> tag like this one. */
    </style>
</head>

<body class="h-full font-sans antialiased">
    <div x-data="{ 
        sidebarOpen: true,
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            document.body.style.overflow = this.sidebarOpen ? 'hidden' : '';
        },
        closeSidebar() {
            this.sidebarOpen = false;
            document.body.style.overflow = '';
        }
    }" class="min-h-screen bg-gray-50">

        <!-- ========================================== -->
        <!-- MOBILE OVERLAY -->
        <!-- ========================================== -->
        <div x-show="sidebarOpen" x-cloak
            @click="closeSidebar()"
            class="sidebar-overlay"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
        </div>

        <!-- ========================================== -->
        <!-- SIDEBAR -->
        <!-- ========================================== -->
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 shadow-lg transform transition-transform duration-300 ease-in-out"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            <!-- Brand -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                <div class="flex items-center">
                    @if($siteLogo)
                        <img src="{{ asset($siteLogo) }}" alt="{{ $siteName }}" class="h-8 w-auto max-w-[7rem] object-contain">
                    @else
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ substr($siteName, 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="ml-2 text-xl font-bold text-gray-800">{{ $siteName }}</span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="px-2 py-3 overflow-y-auto h-[calc(100vh-4rem)] text-sm">

                <!-- Dashboard: always visible, no accordion needed for a single link -->
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-3 py-2 rounded-lg font-medium transition-colors duration-150
                  {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-home w-5 text-lg text-green-600"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                @php
                    $sidebarSections = [
                        [
                            'key' => 'inventory', 'label' => 'Inventory', 'icon' => 'fa-warehouse', 'color' => 'text-yellow-600',
                            'active' => request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.inventory.*'),
                            'links' => [
                                ['route' => 'admin.products.index', 'is' => 'admin.products.*', 'icon' => 'fa-box', 'label' => 'Products', 'module' => 'products'],
                                ['route' => 'admin.categories.index', 'is' => 'admin.categories.*', 'icon' => 'fa-tags', 'label' => 'Categories', 'module' => 'categories'],
                                ['route' => 'admin.inventory.dashboard', 'is' => 'admin.inventory.dashboard', 'icon' => 'fa-warehouse', 'label' => 'Inventory', 'module' => 'inventory'],
                                ['route' => 'admin.inventory.adjustments.index', 'is' => 'admin.inventory.adjustments.*', 'icon' => 'fa-sliders-h', 'label' => 'Stock Adjustments', 'module' => 'inventory'],
                                ['route' => 'admin.inventory.history', 'is' => 'admin.inventory.history', 'icon' => 'fa-history', 'label' => 'Stock History', 'module' => 'inventory'],
                                ['route' => 'admin.products.low-stock', 'is' => 'admin.products.low-stock', 'icon' => 'fa-exclamation-triangle', 'label' => 'Low Stock Alert', 'badge' => App\Models\Product::lowStock()->count(), 'badgeColor' => 'bg-red-500', 'module' => 'products'],
                            ],
                        ],
                        [
                            'key' => 'purchases', 'label' => 'Purchases', 'icon' => 'fa-shopping-cart', 'color' => 'text-purple-700',
                            'active' => request()->routeIs('admin.suppliers.*', 'admin.purchases.*', 'admin.purchase-returns.*'),
                            'links' => [
                                ['route' => 'admin.suppliers.index', 'is' => 'admin.suppliers.*', 'icon' => 'fa-truck', 'label' => 'Suppliers', 'module' => 'suppliers'],
                                ['route' => 'admin.purchases.index', 'is' => 'admin.purchases.*', 'icon' => 'fa-shopping-cart', 'label' => 'Purchases', 'module' => 'purchases'],
                                ['route' => 'admin.purchase-returns.index', 'is' => 'admin.purchase-returns.*', 'icon' => 'fa-undo-alt', 'label' => 'Purchase Returns', 'module' => 'purchase-returns'],
                            ],
                        ],
                        [
                            'key' => 'sales', 'label' => 'Sales', 'icon' => 'fa-shopping-bag', 'color' => 'text-emerald-600',
                            'active' => request()->routeIs('admin.customers.*', 'admin.sales.*', 'admin.sales-returns.*'),
                            'links' => [
                                ['route' => 'admin.customers.index', 'is' => 'admin.customers.*', 'icon' => 'fa-users', 'label' => 'Customers', 'module' => 'customers'],
                                ['route' => 'admin.sales.index', 'is' => 'admin.sales.*', 'icon' => 'fa-shopping-bag', 'label' => 'Sales', 'module' => 'sales'],
                                ['route' => 'admin.sales-returns.index', 'is' => 'admin.sales-returns.*', 'icon' => 'fa-undo-alt', 'label' => 'Sales Returns', 'module' => 'sales-returns'],
                            ],
                        ],
                        [
                            'key' => 'agents', 'label' => 'Agents', 'icon' => 'fa-user-tie', 'color' => 'text-teal-500',
                            'active' => request()->routeIs('admin.agents.*'),
                            'links' => [
                                ['route' => 'admin.agents.index', 'is' => 'admin.agents.*', 'icon' => 'fa-user-tie', 'label' => 'Agents', 'badge' => $pendingAgents = App\Models\User::where('role', 'sales_agent')->where('is_active', false)->whereNull('approved_at')->count(), 'badgeColor' => 'bg-yellow-500', 'module' => 'agents'],
                                ['route' => 'admin.agents.pending', 'is' => 'admin.agents.pending', 'icon' => 'fa-clock', 'label' => 'Pending Approvals', 'badge' => $pendingAgents, 'badgeColor' => 'bg-yellow-500', 'module' => 'agents'],
                            ],
                        ],
                        [
                            'key' => 'hr', 'label' => 'HR', 'icon' => 'fa-id-badge', 'color' => 'text-indigo-500',
                            'active' => request()->routeIs('admin.employees.*', 'admin.departments.*', 'admin.leave-types.*', 'admin.leave-requests.*', 'admin.my-leave.*', 'admin.salary-components.*', 'admin.payroll-runs.*', 'admin.my-payslips.*'),
                            'links' => [
                                ['route' => 'admin.employees.index', 'is' => 'admin.employees.*', 'icon' => 'fa-users', 'label' => 'Employees', 'module' => 'employees'],
                                ['route' => 'admin.departments.index', 'is' => 'admin.departments.*', 'icon' => 'fa-sitemap', 'label' => 'Departments', 'module' => 'employees'],
                                ['route' => 'admin.leave-requests.index', 'is' => 'admin.leave-requests.*', 'icon' => 'fa-calendar-check', 'label' => 'Leave Requests', 'module' => 'leaves'],
                                ['route' => 'admin.leave-types.index', 'is' => 'admin.leave-types.*', 'icon' => 'fa-list', 'label' => 'Leave Types', 'module' => 'leaves'],
                                ['route' => 'admin.my-leave.index', 'is' => 'admin.my-leave.*', 'icon' => 'fa-calendar-day', 'label' => 'My Leave'],
                                ['route' => 'admin.salary-components.index', 'is' => 'admin.salary-components.*', 'icon' => 'fa-money-check-alt', 'label' => 'Salary Structures', 'module' => 'payroll'],
                                ['route' => 'admin.payroll-runs.index', 'is' => 'admin.payroll-runs.*', 'icon' => 'fa-money-bill-wave', 'label' => 'Payroll Runs', 'module' => 'payroll'],
                                ['route' => 'admin.my-payslips.index', 'is' => 'admin.my-payslips.*', 'icon' => 'fa-file-invoice-dollar', 'label' => 'My Payslips'],
                            ],
                        ],
                        [
                            'key' => 'finance', 'label' => 'Finance', 'icon' => 'fa-wallet', 'color' => 'text-green-600',
                            'active' => request()->routeIs('admin.incomes.*', 'admin.income-categories.*', 'admin.expenses.*', 'admin.expense-categories.*', 'admin.money-transfers.*'),
                            'links' => [
                                ['route' => 'admin.incomes.index', 'is' => 'admin.incomes.*', 'icon' => 'fa-arrow-up', 'label' => 'Income', 'iconColor' => 'text-green-600', 'module' => 'incomes'],
                                ['route' => 'admin.income-categories.index', 'is' => 'admin.income-categories.*', 'icon' => 'fa-tags', 'label' => 'Income Categories', 'iconColor' => 'text-green-600', 'module' => 'incomes'],
                                ['route' => 'admin.expenses.index', 'is' => 'admin.expenses.*', 'icon' => 'fa-arrow-down', 'label' => 'Expenses', 'iconColor' => 'text-red-600', 'module' => 'expenses'],
                                ['route' => 'admin.expense-categories.index', 'is' => 'admin.expense-categories.*', 'icon' => 'fa-tags', 'label' => 'Expense Categories', 'iconColor' => 'text-red-600', 'module' => 'expenses'],
                                ['route' => 'admin.money-transfers.index', 'is' => 'admin.money-transfers.*', 'icon' => 'fa-exchange-alt', 'label' => 'Money Transfer', 'iconColor' => 'text-blue-600', 'module' => 'money-transfers'],
                            ],
                        ],
                        [
                            'key' => 'accounting', 'label' => 'Accounting', 'icon' => 'fa-book', 'color' => 'text-cyan-500',
                            'active' => request()->routeIs('admin.accounts.*', 'admin.bank-reconciliations.*'),
                            'links' => [
                                ['route' => 'admin.accounts.index', 'is' => 'admin.accounts.*', 'icon' => 'fa-book', 'label' => 'Chart of Accounts', 'module' => 'accounts'],
                                ['route' => 'admin.bank-reconciliations.index', 'is' => 'admin.bank-reconciliations.*', 'icon' => 'fa-university', 'label' => 'Bank Reconciliation', 'module' => 'bank-reconciliations'],
                            ],
                        ],
                        [
                            'key' => 'reports', 'label' => 'Reports', 'icon' => 'fa-chart-bar', 'color' => 'text-gray-600',
                            'active' => request()->routeIs('admin.reports.*'),
                            'links' => [
                                ['route' => 'admin.reports.profit-loss', 'is' => 'admin.reports.profit-loss', 'icon' => 'fa-chart-bar', 'label' => 'Profit & Loss', 'module' => 'reports'],
                                ['route' => 'admin.reports.trial-balance', 'is' => 'admin.reports.trial-balance', 'icon' => 'fa-balance-scale', 'label' => 'Trial Balance', 'module' => 'reports'],
                                ['route' => 'admin.reports.customers', 'is' => 'admin.reports.customers', 'icon' => 'fa-users', 'label' => 'Customers', 'module' => 'reports'],
                                ['route' => 'admin.reports.receivable', 'is' => 'admin.reports.receivable', 'icon' => 'fa-hand-holding-usd', 'label' => 'Receivable', 'module' => 'reports'],
                                ['route' => 'admin.reports.payable', 'is' => 'admin.reports.payable', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Payable', 'module' => 'reports'],
                                ['route' => 'admin.reports.suppliers', 'is' => 'admin.reports.suppliers', 'icon' => 'fa-truck', 'label' => 'Suppliers', 'module' => 'reports'],
                                ['route' => 'admin.reports.day-book', 'is' => 'admin.reports.day-book', 'icon' => 'fa-book', 'label' => 'Day Book', 'module' => 'reports'],
                                ['route' => 'admin.reports.expenses', 'is' => 'admin.reports.expenses', 'icon' => 'fa-arrow-down', 'label' => 'Expenses', 'iconColor' => 'text-red-500', 'module' => 'reports'],
                                ['route' => 'admin.reports.incomes', 'is' => 'admin.reports.incomes', 'icon' => 'fa-arrow-up', 'label' => 'Income', 'iconColor' => 'text-green-500', 'module' => 'reports'],
                                ['route' => 'admin.reports.agents', 'is' => 'admin.reports.agents', 'icon' => 'fa-user-tie', 'label' => 'Agents', 'module' => 'reports'],
                                ['route' => 'admin.reports.daily-summary', 'is' => 'admin.reports.daily-summary', 'icon' => 'fa-calendar-day', 'label' => 'Daily Summary', 'module' => 'reports'],
                                ['route' => 'admin.reports.tax', 'is' => 'admin.reports.tax', 'icon' => 'fa-percentage', 'label' => 'Tax Report', 'module' => 'reports'],
                            ],
                        ],
                        [
                            'key' => 'system', 'label' => 'System', 'icon' => 'fa-cogs', 'color' => 'text-gray-600',
                            'active' => request()->routeIs('admin.activity-logs.*', 'admin.backups.*', 'admin.system.api.*', 'admin.guide.index'),
                            'links' => [
                                ['route' => 'admin.guide.index', 'is' => 'admin.guide.index', 'icon' => 'fa-book-open', 'label' => 'Guide Book (SOPs)'],
                                ['route' => 'admin.activity-logs.index', 'is' => 'admin.activity-logs.*', 'icon' => 'fa-history', 'label' => 'Activity Logs', 'module' => 'activity-logs'],
                                ['route' => 'admin.backups.index', 'is' => 'admin.backups.*', 'icon' => 'fa-cloud-upload-alt', 'label' => 'Backup & Restore', 'module' => 'backups'],
                                // These three routes are hard role:admin-gated in routes/web.php
                                // (not part of the role_permissions matrix), so they're filtered
                                // by isAdmin() below, not hasPermission().
                                ['route' => 'admin.system.api.docs', 'is' => 'admin.system.api.docs', 'icon' => 'fa-book', 'label' => 'API Documentation', 'adminOnly' => true],
                                ['route' => 'admin.system.api.tester', 'is' => 'admin.system.api.tester', 'icon' => 'fa-flask', 'label' => 'API Testing', 'adminOnly' => true],
                            ],
                        ],
                    ];

                    if (auth()->user() && auth()->user()->isAdmin()) {
                        $sidebarSections[] = [
                            'key' => 'settings', 'label' => 'Settings', 'icon' => 'fa-cog', 'color' => 'text-gray-500',
                            'active' => request()->routeIs('admin.settings.*'),
                            'links' => [
                                ['route' => 'admin.settings.general', 'is' => 'admin.settings.general', 'icon' => 'fa-sliders-h', 'label' => 'General'],
                                ['route' => 'admin.settings.customer-groups.index', 'is' => 'admin.settings.customer-groups.*', 'icon' => 'fa-layer-group', 'label' => 'Customer Groups'],
                                ['route' => 'admin.settings.users.index', 'is' => 'admin.settings.users.*', 'icon' => 'fa-users-cog', 'label' => 'Users'],
                                ['route' => 'admin.settings.permissions.index', 'is' => 'admin.settings.permissions.*', 'icon' => 'fa-shield-alt', 'label' => 'Permissions'],
                                ['route' => 'admin.settings.commission', 'is' => 'admin.settings.commission', 'icon' => 'fa-percentage', 'label' => 'Commission & Bonus'],
                            ],
                        ];
                    }

                    // Hide any link the current user can't actually reach (module
                    // permission is view=false, or it's one of the hard
                    // role:admin-only routes above), then drop any section left
                    // with zero visible links. Without this, enabling real
                    // permission:module,view enforcement on the routes below
                    // would turn every hidden-but-linked page into a dead 403
                    // click for manager/accountant.
                    $user = auth()->user();
                    $sidebarSections = array_values(array_filter(array_map(function ($section) use ($user) {
                        $section['links'] = array_values(array_filter($section['links'], function ($link) use ($user) {
                            if (!empty($link['adminOnly'])) {
                                return $user && $user->isAdmin();
                            }
                            return !isset($link['module']) || ($user && $user->hasPermission($link['module']));
                        }));
                        return $section;
                    }, $sidebarSections), fn ($section) => count($section['links']) > 0));
                @endphp

                @foreach($sidebarSections as $section)
                <div class="mt-1" x-data="{ open: {{ $section['active'] ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center px-3 py-2 rounded-lg text-xs font-semibold uppercase tracking-wider transition-colors duration-150
                          {{ $section['active'] ? 'text-gray-700' : 'text-gray-400 hover:text-gray-600' }}">
                        <i class="fas {{ $section['icon'] }} w-5 text-sm {{ $section['color'] }}"></i>
                        <span class="ml-3">{{ $section['label'] }}</span>
                        <i class="fas fa-chevron-down ml-auto text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-1 mt-1" x-cloak>
                        @foreach($section['links'] as $link)
                        <a href="{{ route($link['route']) }}"
                            class="flex items-center pl-8 pr-3 py-1.5 rounded-lg font-medium transition-colors duration-150
                              {{ request()->routeIs($link['is']) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            <i class="fas {{ $link['icon'] }} w-4 text-xs {{ $link['iconColor'] ?? $section['color'] }}"></i>
                            <span class="ml-2.5">{{ $link['label'] }}</span>
                            @if(!empty($link['badge']))
                            <span class="ml-auto {{ $link['badgeColor'] }} text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $link['badge'] }}</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </nav>
        </div>

        <!-- ========================================== -->
        <!-- MAIN CONTENT -->
        <!-- ========================================== -->
        <div class="flex-1 lg:ml-64">

            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center">
                        <button @click="toggleSidebar()" class="text-gray-500 hover:text-gray-700 lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="ml-2 text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-1 sm:gap-3">
                    <!-- Quick Search (Ctrl/Cmd+K) -->
                    <button type="button" @click="$store.wserpUi.searchOpen = true" title="Quick search (Ctrl/Cmd+K)"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-search"></i>
                    </button>

                    @php
                        $quickAddItems = array_values(array_filter([
                            ['route' => 'admin.sales.create', 'icon' => 'fa-shopping-bag', 'color' => 'text-emerald-600', 'label' => 'New Sale', 'module' => 'sales'],
                            ['route' => 'admin.purchases.create', 'icon' => 'fa-shopping-cart', 'color' => 'text-purple-700', 'label' => 'New Purchase', 'module' => 'purchases'],
                            ['route' => 'admin.products.create', 'icon' => 'fa-box', 'color' => 'text-yellow-600', 'label' => 'New Product', 'module' => 'products'],
                            ['route' => 'admin.customers.create', 'icon' => 'fa-users', 'color' => 'text-emerald-600', 'label' => 'New Customer', 'module' => 'customers'],
                            ['route' => 'admin.suppliers.create', 'icon' => 'fa-truck', 'color' => 'text-purple-700', 'label' => 'New Supplier', 'module' => 'suppliers'],
                            ['route' => 'admin.expenses.create', 'icon' => 'fa-arrow-down', 'color' => 'text-red-600', 'label' => 'New Expense', 'module' => 'expenses'],
                            ['route' => 'admin.incomes.create', 'icon' => 'fa-arrow-up', 'color' => 'text-green-600', 'label' => 'New Income', 'module' => 'incomes'],
                        ], fn ($item) => auth()->user() && auth()->user()->hasPermission($item['module'], 'create')));
                    @endphp

                    <!-- Quick Add -->
                    @if(count($quickAddItems))
                    <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                        <button @click="open = !open" title="Quick add"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-plus"></i>
                        </button>
                        <div x-show="open" x-cloak x-transition:enter.duration.150ms x-transition:leave.duration.100ms
                            class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <p class="px-4 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Quick Add</p>
                            @foreach($quickAddItems as $item)
                            <a href="{{ route($item['route']) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas {{ $item['icon'] }} w-5 {{ $item['color'] }}"></i>
                                <span class="ml-3">{{ $item['label'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @php
                        $notifItems = array_values(array_filter([
                            (auth()->user() && auth()->user()->hasPermission('products', 'view')) ? [
                                'label' => 'Low Stock', 'icon' => 'fa-exclamation-triangle', 'color' => 'text-red-500',
                                'count' => App\Models\Product::lowStock()->count(),
                                'route' => 'admin.products.low-stock',
                                'text' => 'product(s) at or below their minimum stock level',
                            ] : null,
                            (auth()->user() && auth()->user()->hasPermission('agents', 'view')) ? [
                                'label' => 'Pending Agents', 'icon' => 'fa-user-clock', 'color' => 'text-yellow-500',
                                'count' => App\Models\User::where('role', 'sales_agent')->where('is_active', false)->whereNull('approved_at')->count(),
                                'route' => 'admin.agents.pending',
                                'text' => 'sales agent registration(s) awaiting approval',
                            ] : null,
                            (auth()->user() && auth()->user()->hasPermission('leaves', 'view')) ? [
                                'label' => 'Pending Leave', 'icon' => 'fa-calendar-check', 'color' => 'text-indigo-500',
                                'count' => App\Models\LeaveRequest::where('status', 'pending')->count(),
                                'route' => 'admin.leave-requests.index', 'params' => ['status' => 'pending'],
                                'text' => 'leave request(s) awaiting approval',
                            ] : null,
                        ]));
                        $notifTotal = collect($notifItems)->sum('count');
                    @endphp

                    <!-- Notifications -->
                    @if(count($notifItems))
                    <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                        <button @click="open = !open" title="Notifications"
                            class="relative w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-bell"></i>
                            @if($notifTotal > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full leading-none">{{ $notifTotal > 99 ? '99+' : $notifTotal }}</span>
                            @endif
                        </button>
                        <div x-show="open" x-cloak x-transition:enter.duration.150ms x-transition:leave.duration.100ms
                            class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <p class="px-4 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Notifications</p>
                            @if($notifTotal === 0)
                            <p class="px-4 py-4 text-sm text-gray-400 text-center"><i class="fas fa-check-circle text-green-400 mr-1"></i> All caught up</p>
                            @else
                                @foreach($notifItems as $item)
                                @if($item['count'] > 0)
                                <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="flex items-start px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas {{ $item['icon'] }} w-5 mt-0.5 {{ $item['color'] }}"></i>
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900">{{ $item['count'] }} {{ $item['label'] }}</span>
                                        <span class="block text-xs text-gray-500">{{ $item['text'] }}</span>
                                    </span>
                                </a>
                                @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Fullscreen toggle -->
                    <div x-data="{ fs: false }" @fullscreenchange.window="fs = !!document.fullscreenElement" class="hidden sm:block">
                        <button type="button" title="Toggle fullscreen"
                            @click="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-expand" x-show="!fs"></i>
                            <i class="fas fa-compress" x-show="fs" x-cloak></i>
                        </button>
                    </div>

                    @if(auth()->user() && auth()->user()->isAdmin())
                    <a href="{{ route('admin.ledger-integrity.index') }}" title="Reconcile All Accounts"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50">
                        <i class="fas fa-heartbeat"></i>
                    </a>
                    @endif

                    <!-- Keyboard shortcuts help -->
                    <button type="button" @click="$store.wserpUi.shortcutsOpen = true" title="Keyboard shortcuts (?)"
                        class="hidden sm:flex w-9 h-9 items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-keyboard"></i>
                    </button>

                    @if($darkModeEnabled ?? true)
                    <button type="button" onclick="wserpToggleTheme()" title="Toggle dark mode"
                        class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    @endif

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                {{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
                            </div>
                            <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user() ? auth()->user()->name : 'Admin' }}</span>
                            <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition:enter.duration.200ms x-transition:leave.duration.150ms class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <a href="{{ route('admin.profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-user w-5 text-gray-400"></i><span class="ml-3">My Profile</span></a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}" class="block">@csrf<button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt w-5 text-red-400"></i><span class="ml-3">Logout</span></button></form>
                        </div>
                    </div>
                    </div>
                </div>
            </header>

            @php
                $shortcutItems = array_values(array_filter([
                    ['key' => 'd', 'route' => 'admin.dashboard', 'label' => 'Dashboard'],
                    ['key' => 's', 'route' => 'admin.sales.index', 'label' => 'Sales', 'module' => 'sales'],
                    ['key' => 'p', 'route' => 'admin.purchases.index', 'label' => 'Purchases', 'module' => 'purchases'],
                    ['key' => 'i', 'route' => 'admin.products.index', 'label' => 'Products (Inventory)', 'module' => 'products'],
                    ['key' => 'c', 'route' => 'admin.customers.index', 'label' => 'Customers', 'module' => 'customers'],
                    ['key' => 'v', 'route' => 'admin.suppliers.index', 'label' => 'Suppliers (Vendors)', 'module' => 'suppliers'],
                    ['key' => 'e', 'route' => 'admin.expenses.index', 'label' => 'Expenses', 'module' => 'expenses'],
                    ['key' => 'n', 'route' => 'admin.incomes.index', 'label' => 'Income', 'module' => 'incomes'],
                    ['key' => 'r', 'route' => 'admin.reports.profit-loss', 'label' => 'Reports', 'module' => 'reports'],
                    ['key' => 'h', 'route' => 'admin.employees.index', 'label' => 'Employees (HR)', 'module' => 'employees'],
                    ['key' => 'a', 'route' => 'admin.accounts.index', 'label' => 'Chart of Accounts', 'module' => 'accounts'],
                ], fn ($item) => !isset($item['module']) || (auth()->user() && auth()->user()->hasPermission($item['module'], 'view'))));
                $shortcutMap = collect($shortcutItems)->mapWithKeys(fn ($item) => [$item['key'] => route($item['route'])])->toArray();
            @endphp

            <!-- ========================================== -->
            <!-- QUICK SEARCH PALETTE (Ctrl/Cmd+K) -->
            <!-- ========================================== -->
            <div x-data="{
                    query: '',
                    groups: [],
                    loading: false,
                    activeIndex: 0,
                    debounceTimer: null,
                    flatItems() {
                        let items = [];
                        this.groups.forEach(g => g.items.forEach(it => items.push(it)));
                        return items;
                    },
                    async runSearch() {
                        const q = this.query.trim();
                        if (q.length < 2) { this.groups = []; return; }
                        this.loading = true;
                        try {
                            const res = await fetch('{{ route('admin.quick-search') }}?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const data = await res.json();
                            this.groups = data.groups || [];
                            this.activeIndex = 0;
                        } catch (e) {
                            this.groups = [];
                        } finally {
                            this.loading = false;
                        }
                    },
                    debouncedSearch() {
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(() => this.runSearch(), 250);
                    },
                    go(index) {
                        const items = this.flatItems();
                        if (items[index]) window.location.href = items[index].url;
                    },
                    moveActive(delta) {
                        const items = this.flatItems();
                        if (!items.length) return;
                        this.activeIndex = (this.activeIndex + delta + items.length) % items.length;
                    },
                }"
                x-show="$store.wserpUi.searchOpen" x-cloak
                x-effect="if ($store.wserpUi.searchOpen) { query = ''; groups = []; activeIndex = 0; $nextTick(() => $refs.searchInput && $refs.searchInput.focus()); }"
                @keydown.escape.window="$store.wserpUi.searchOpen = false"
                class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="$store.wserpUi.searchOpen = false"></div>
                <div class="relative max-w-xl mx-auto mt-20 px-4">
                    <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                        <div class="flex items-center px-4 py-3 border-b border-gray-100">
                            <i class="fas fa-search text-gray-400"></i>
                            <input type="text" x-ref="searchInput" x-model="query" @input="debouncedSearch()"
                                @keydown.down.prevent="moveActive(1)" @keydown.up.prevent="moveActive(-1)"
                                @keydown.enter.prevent="go(activeIndex)"
                                placeholder="Search customers, products, suppliers, sales & purchase invoices..."
                                class="flex-1 ml-3 outline-none text-sm text-gray-800 placeholder-gray-400">
                            <kbd class="hidden sm:inline px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 bg-gray-100 rounded border border-gray-200">ESC</kbd>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="loading">
                                <p class="px-4 py-6 text-sm text-gray-400 text-center"><i class="fas fa-spinner fa-spin mr-1"></i> Searching...</p>
                            </template>
                            <template x-if="!loading && query.trim().length >= 2 && groups.length === 0">
                                <p class="px-4 py-6 text-sm text-gray-400 text-center">No results for "<span x-text="query"></span>"</p>
                            </template>
                            <template x-if="!loading && query.trim().length < 2">
                                <p class="px-4 py-6 text-sm text-gray-400 text-center">Type at least 2 characters to search.</p>
                            </template>
                            <template x-for="group in groups" :key="group.label">
                                <div>
                                    <p class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider" x-text="group.label"></p>
                                    <template x-for="item in group.items" :key="item.url">
                                        <a :href="item.url"
                                            class="flex items-center px-4 py-2.5 text-sm"
                                            :class="(flatItems().indexOf(item) === activeIndex) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'">
                                            <i class="fas" :class="group.icon + ' ' + group.color"></i>
                                            <span class="ml-3 flex-1 min-w-0">
                                                <span class="block font-medium truncate" x-text="item.title"></span>
                                                <span class="block text-xs text-gray-400 truncate" x-text="item.subtitle"></span>
                                            </span>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- KEYBOARD SHORTCUTS HELP -->
            <!-- ========================================== -->
            <div x-show="$store.wserpUi.shortcutsOpen" x-cloak
                @keydown.escape.window="$store.wserpUi.shortcutsOpen = false"
                class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="$store.wserpUi.shortcutsOpen = false"></div>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-keyboard text-gray-400 mr-2"></i> Keyboard Shortcuts</h3>
                            <button @click="$store.wserpUi.shortcutsOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="space-y-2 text-sm max-h-96 overflow-y-auto">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Quick search</span>
                                <span><kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">K</kbd></span>
                            </div>
                            @foreach($shortcutItems as $item)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">{{ $item['label'] }}</span>
                                <span><kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">G</kbd> <kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">{{ strtoupper($item['key']) }}</kbd></span>
                            </div>
                            @endforeach
                            <div class="flex items-center justify-between pt-2 mt-2 border-t border-gray-100">
                                <span class="text-gray-600">Show this help</span>
                                <span><kbd class="px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded border border-gray-200">?</kbd></span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-4">Shortcuts are ignored while typing in a text field.</p>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-4 lg:p-6">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                    <div class="flex items-center"><i class="fas fa-check-circle text-green-400 text-xl"></i>
                        <p class="ml-3 text-green-700">{{ session('success') }}</p><button @click="show = false" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                @endif
                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                    <div class="flex items-center"><i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                        <p class="ml-3 text-red-700">{{ session('error') }}</p><button @click="show = false" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Remove @livewireScripts -->
    {{-- @livewireScripts --}}
    @vite(['resources/js/app.js'])
    <script>
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $.fn.DataTable !== 'undefined') {
                $.extend(true, $.fn.dataTable.defaults, {
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        zeroRecords: "No matching records found"
                    }
                });
            }
        });
    </script>

    <!-- Quick search / keyboard shortcuts store + global hotkeys -->
    <script>
        const wserpShortcutMap = @json($shortcutMap ?? []);

        document.addEventListener('alpine:init', () => {
            Alpine.store('wserpUi', { searchOpen: false, shortcutsOpen: false });
        });

        document.addEventListener('DOMContentLoaded', function () {
            let chordArmed = false;
            let chordTimer = null;

            function isTyping(el) {
                if (!el) return false;
                const tag = el.tagName;
                return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
            }

            document.addEventListener('keydown', function (e) {
                // Ctrl/Cmd+K - quick search, works everywhere (even while typing).
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    chordArmed = false;
                    Alpine.store('wserpUi').searchOpen = true;
                    return;
                }

                const ui = Alpine.store('wserpUi');
                const modalOpen = ui.searchOpen || ui.shortcutsOpen;

                // Never hijack typing, an open modal, or any other modifier combo.
                if (isTyping(e.target) || modalOpen || e.ctrlKey || e.metaKey || e.altKey) {
                    chordArmed = false;
                    return;
                }

                if (chordArmed) {
                    chordArmed = false;
                    clearTimeout(chordTimer);
                    const url = wserpShortcutMap[e.key.toLowerCase()];
                    if (url) {
                        e.preventDefault();
                        window.location.href = url;
                    }
                    return;
                }

                if (e.key === 'g') {
                    chordArmed = true;
                    clearTimeout(chordTimer);
                    chordTimer = setTimeout(() => { chordArmed = false; }, 1200);
                    return;
                }

                if (e.key === '?') {
                    e.preventDefault();
                    Alpine.store('wserpUi').shortcutsOpen = true;
                }
            });
        });
    </script>
    @yield('scripts')
    {{-- @push('scripts') on 36+ pages (DataTable init, charts, etc.) needs a
    matching @stack to actually render - it was building up in Blade's stack
    registry and being silently discarded with no @stack anywhere in this
    layout. --}}
    @stack('scripts')
</body>

</html>
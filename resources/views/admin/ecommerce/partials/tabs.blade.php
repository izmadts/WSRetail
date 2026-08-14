<div class="mb-6 border-b border-gray-200">
    <nav class="flex flex-wrap gap-1 -mb-px">
        <a href="{{ route('admin.settings.ecommerce.store') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.ecommerce.store*') ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-store mr-1"></i> Store
        </a>
        <a href="{{ route('admin.settings.ecommerce.payment') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.ecommerce.payment*') ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-credit-card mr-1"></i> Payment
        </a>
        <a href="{{ route('admin.settings.ecommerce.shipping') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.ecommerce.shipping*') ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-truck mr-1"></i> Shipping
        </a>
    </nav>
</div>

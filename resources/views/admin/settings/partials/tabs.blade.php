<div class="mb-6 border-b border-gray-200">
    <nav class="flex flex-wrap gap-1 -mb-px">
        <a href="{{ route('admin.settings.general') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.general') || request()->routeIs('admin.settings.index') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-cog mr-1"></i> General
        </a>
        <a href="{{ route('admin.settings.customer-groups.index') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.customer-groups.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-layer-group mr-1"></i> Customer Groups
        </a>
        <a href="{{ route('admin.settings.locations.index') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.locations.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-store mr-1"></i> Locations
        </a>
        <a href="{{ route('admin.settings.pos.edit') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.pos.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-cash-register mr-1"></i> POS Settings
        </a>
        <a href="{{ route('admin.settings.users.index') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.users.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-users-cog mr-1"></i> Users
        </a>
        <a href="{{ route('admin.settings.permissions.index') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.permissions.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-shield-alt mr-1"></i> Permissions
        </a>
        <a href="{{ route('admin.settings.credit') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.credit') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-credit-card mr-1"></i> Credit Settings
        </a>
        <a href="{{ route('admin.settings.ecommerce.store') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.ecommerce.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-shopping-bag mr-1"></i> Ecommerce
        </a>
    </nav>
</div>

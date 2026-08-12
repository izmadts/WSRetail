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
        <a href="{{ route('admin.settings.users.index') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.users.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-users-cog mr-1"></i> Users
        </a>
        <a href="{{ route('admin.settings.permissions.index') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.permissions.*') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-shield-alt mr-1"></i> Permissions
        </a>
        <a href="{{ route('admin.settings.commission') }}"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors duration-150 {{ request()->routeIs('admin.settings.commission') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <i class="fas fa-percentage mr-1"></i> Commission &amp; Bonus
        </a>
    </nav>
</div>

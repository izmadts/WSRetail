@extends('layouts.admin')

@section('title', 'Settings - Permissions')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-shield-alt text-blue-600 mr-2"></i> Role Permissions</h4>
        <p class="text-sm text-gray-500 mt-1">Admin always has full access to everything and isn't shown here. These toggles constrain manager/accountant/sales_agent.</p>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.settings.permissions.update') }}" method="POST">
            @csrf

            <div x-data="{ role: '{{ $roles[0] }}' }">
                <div class="flex flex-wrap gap-1 mb-4 border-b border-gray-200">
                    @foreach($roles as $role)
                    <button type="button" @click="role = '{{ $role }}'"
                        :class="role === '{{ $role }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-150">
                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                    </button>
                    @endforeach
                </div>

                @foreach($roles as $role)
                <div x-show="role === '{{ $role }}'" x-cloak class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Module</th>
                                <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">View</th>
                                <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Create</th>
                                <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Edit</th>
                                <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($modules as $module)
                            @php $cell = $matrix[$role][$module]; @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="py-2 px-2 text-sm font-medium text-gray-700">{{ ucwords(str_replace('-', ' ', $module)) }}</td>
                                @foreach(['can_view', 'can_create', 'can_edit', 'can_delete'] as $ability)
                                <td class="py-2 px-2 text-center">
                                    <input type="checkbox"
                                        name="permissions[{{ $role }}][{{ $module }}][{{ $ability }}]"
                                        value="1"
                                        {{ $cell->{$ability} ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Save Permissions
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

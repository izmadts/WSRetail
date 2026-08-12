@extends('layouts.admin')

@section('title', 'Settings - Users')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-users-cog text-blue-600 mr-2"></i> Staff Users</h4>
        <a href="{{ route('admin.settings.users.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> Add User
        </a>
    </div>
    <p class="px-6 pt-4 text-xs text-gray-500">Sales agents have their own management screen under Agents - this covers admin/manager/accountant accounts.</p>
    <div class="p-6 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Name</th>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Email</th>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Role</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="py-2 px-2 font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="py-2 px-2 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="py-2 px-2 text-sm text-gray-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td class="py-2 px-2 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="py-2 px-2 text-center">
                        <a href="{{ route('admin.settings.users.edit', $user) }}" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200 inline-block">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.settings.users.destroy', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-gray-400">No staff users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

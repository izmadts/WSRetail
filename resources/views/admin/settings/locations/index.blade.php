@extends('layouts.admin')

@section('title', 'Settings - Locations')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-store text-blue-600 mr-2"></i> Locations</h4>
        <a href="{{ route('admin.settings.locations.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> Add Location
        </a>
    </div>
    <div class="p-6 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Name</th>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Address</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">POS Use</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Sales</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($locations as $location)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="py-2 px-2 font-medium text-gray-900">{{ $location->name }}</td>
                    <td class="py-2 px-2 text-sm text-gray-600">{{ $location->address ?? '-' }}</td>
                    <td class="py-2 px-2 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $location->pos_type == 'retail' ? 'bg-blue-100 text-blue-800' : ($location->pos_type == 'wholesale' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700') }}">
                            {{ ucfirst($location->pos_type) }}
                        </span>
                    </td>
                    <td class="py-2 px-2 text-center text-sm text-gray-600">{{ $location->sales_count }}</td>
                    <td class="py-2 px-2 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="py-2 px-2 text-center">
                        <a href="{{ route('admin.settings.locations.edit', $location) }}" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200 inline-block">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                        <form action="{{ route('admin.settings.locations.destroy', $location) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-400">No locations yet. Sales without a location behave as before.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

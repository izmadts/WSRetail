@extends('layouts.admin')

@section('title', 'Settings - Customer Groups')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-layer-group text-blue-600 mr-2"></i> Customer Groups</h4>
        <a href="{{ route('admin.settings.customer-groups.create') }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> Add Group
        </a>
    </div>
    <div class="p-6 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Name</th>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Price Basis</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Discount %</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Customers</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Default</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customerGroups as $group)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="py-2 px-2 font-medium text-gray-900">{{ $group->name }}</td>
                    <td class="py-2 px-2 text-sm text-gray-600">{{ $group->price_field == 'wholesale_price' ? 'Wholesale Price' : 'Retail (Sale) Price' }}</td>
                    <td class="py-2 px-2 text-center text-sm text-gray-600">{{ number_format($group->discount_percent, 2) }}%</td>
                    <td class="py-2 px-2 text-center text-sm text-gray-600">{{ $group->customers_count }}</td>
                    <td class="py-2 px-2 text-center">
                        @if($group->is_default)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Default</span>
                        @endif
                    </td>
                    <td class="py-2 px-2 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $group->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $group->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="py-2 px-2 text-center">
                        <a href="{{ route('admin.settings.customer-groups.edit', $group) }}" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200 inline-block">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                        <form action="{{ route('admin.settings.customer-groups.destroy', $group) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-8 text-center text-gray-400">No customer groups yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

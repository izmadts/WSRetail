@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Product Categories')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-tags text-gray-400 mr-2"></i> All Categories
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $categories->count() }} total</span>
        </div>
        <a href="{{ route('admin.categories.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> Add Category
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="categoriesTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Name</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Description</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Parent</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Products</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($categories as $category)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $category->name }}</span>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ Str::limit($category->description, 50) }}
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $category->parent->name ?? '-' }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $category->products->count() }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.categories.show', $category) }}"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                    class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 {{ $category->is_active ? 'text-gray-600 hover:bg-gray-100' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors duration-200">
                                        <i class="fas {{ $category->is_active ? 'fa-pause' : 'fa-play' }} text-sm"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('categoriesTable')) {
            new DataTable('#categoriesTable', {
                pageLength: 25,
                responsive: true,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        }
    });
</script>
@endpush
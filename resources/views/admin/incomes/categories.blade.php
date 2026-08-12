@extends('layouts.admin')

@section('title', 'Income Categories')
@section('page-title', 'Income Categories')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Category Form -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-plus-circle text-green-600 mr-2"></i> Add Category
            </h4>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.income-categories.store') }}" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-tags text-gray-400 mr-2"></i> Categories
                <span class="ml-2 text-sm text-gray-500">{{ $categories->count() }} total</span>
            </h4>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Name</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Description</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-2 px-2 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="py-2 px-2 text-sm text-gray-600">{{ $category->description ?? '-' }}</td>
                            <td class="py-2 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-2 px-2 text-center">
                                <!-- Edit Button -->
                                <button type="button" @click="openEditModal({{ $category->id }})" 
                                        class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                
                                <!-- Delete Form -->
                                <form action="{{ route('admin.income-categories.destroy', $category) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" 
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- Edit Modal (Alpine.js) -->
<!-- ========================================== -->
<div x-data="{ showEditModal: false, categoryId: null, categoryName: '', categoryDescription: '', categoryActive: false }" 
     x-init="
        window.openEditModal = function(id, name, description, active) {
            categoryId = id;
            categoryName = name;
            categoryDescription = description || '';
            categoryActive = active || false;
            showEditModal = true;
        }
     ">
    
    <!-- Modal Overlay -->
    <div x-show="showEditModal" 
         x-transition:enter.duration.300ms x-transition:leave.duration.300ms
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showEditModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Category
                    </h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('admin/income-categories') }}/' + categoryId" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="categoryName" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" x-model="categoryDescription"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" x-model="categoryActive"
                                       class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                                <i class="fas fa-save mr-1"></i> Update
                            </button>
                            <button type="button" @click="showEditModal = false" 
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Any additional JS can go here
    });
</script>
@endpush
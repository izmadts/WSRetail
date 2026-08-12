@extends('layouts.admin')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
<div x-data="{
    showModal: false,
    editing: false,
    departmentId: null,
    name: '',
    description: '',
    isActive: true,
    openCreate() {
        this.editing = false; this.departmentId = null;
        this.name = ''; this.description = ''; this.isActive = true;
        this.showModal = true;
    },
    openEdit(d) {
        this.editing = true; this.departmentId = d.id;
        this.name = d.name; this.description = d.description || ''; this.isActive = !!d.is_active;
        this.showModal = true;
    },
}">
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-sitemap text-gray-400 mr-2"></i> Departments</span>
            <span class="ml-2 text-sm text-gray-500">{{ $departments->count() }} total</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.employees.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> Employees
            </a>
            @if(auth()->user()->hasPermission('employees', 'create'))
            <button type="button" @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Add Department
            </button>
            @endif
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Name</th>
                        <th class="text-left py-3 px-2">Description</th>
                        <th class="text-center py-3 px-2">Employees</th>
                        <th class="text-center py-3 px-2">Status</th>
                        <th class="text-center py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $department)
                    @php
                        $departmentData = ['id' => $department->id, 'name' => $department->name, 'description' => $department->description, 'is_active' => $department->is_active];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $department->name }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $department->description ?? '-' }}</td>
                        <td class="py-3 px-2 text-center">{{ $department->employees_count }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                @if(auth()->user()->hasPermission('employees', 'edit'))
                                <button type="button" @click='openEdit(@json($departmentData))' class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                @endif
                                @if(auth()->user()->hasPermission('employees', 'delete'))
                                <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this department?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">No departments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editing ? 'Edit Department' : 'New Department'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editing ? '{{ url('admin/departments') }}/' + departmentId : '{{ route('admin.departments.store') }}'" method="POST">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" x-model="description" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
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

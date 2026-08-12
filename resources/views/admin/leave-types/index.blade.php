@extends('layouts.admin')

@section('title', 'Leave Types')
@section('page-title', 'Leave Types')

@section('content')
<div x-data="{
    showModal: false,
    editing: false,
    leaveTypeId: null,
    name: '',
    description: '',
    defaultDays: 0,
    isPaid: true,
    isActive: true,
    openCreate() {
        this.editing = false; this.leaveTypeId = null;
        this.name = ''; this.description = ''; this.defaultDays = 0; this.isPaid = true; this.isActive = true;
        this.showModal = true;
    },
    openEdit(t) {
        this.editing = true; this.leaveTypeId = t.id;
        this.name = t.name; this.description = t.description || '';
        this.defaultDays = t.default_days_per_year; this.isPaid = !!t.is_paid; this.isActive = !!t.is_active;
        this.showModal = true;
    },
}">
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-list text-gray-400 mr-2"></i> Leave Types</span>
            <span class="ml-2 text-sm text-gray-500">{{ $leaveTypes->count() }} total</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.leave-requests.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-calendar-check mr-1"></i> Leave Requests
            </a>
            @if(auth()->user()->hasPermission('leaves', 'create'))
            <button type="button" @click="openCreate()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Add Leave Type
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
                        <th class="text-center py-3 px-2">Days/Year</th>
                        <th class="text-center py-3 px-2">Paid</th>
                        <th class="text-center py-3 px-2">Requests</th>
                        <th class="text-center py-3 px-2">Status</th>
                        <th class="text-center py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leaveTypes as $leaveType)
                    @php
                        $leaveTypeData = ['id' => $leaveType->id, 'name' => $leaveType->name, 'description' => $leaveType->description, 'default_days_per_year' => $leaveType->default_days_per_year, 'is_paid' => $leaveType->is_paid, 'is_active' => $leaveType->is_active];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $leaveType->name }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $leaveType->description ?? '-' }}</td>
                        <td class="py-3 px-2 text-center">{{ $leaveType->default_days_per_year }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leaveType->is_paid ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">{{ $leaveType->leave_requests_count }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leaveType->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                @if(auth()->user()->hasPermission('leaves', 'edit'))
                                <button type="button" @click='openEdit(@json($leaveTypeData))' class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                @endif
                                @if(auth()->user()->hasPermission('leaves', 'delete'))
                                <form action="{{ route('admin.leave-types.destroy', $leaveType) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this leave type?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">No leave types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Leave Type Modal -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editing ? 'Edit Leave Type' : 'New Leave Type'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editing ? '{{ url('admin/leave-types') }}/' + leaveTypeId : '{{ route('admin.leave-types.store') }}'" method="POST">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Days / Year <span class="text-red-500">*</span></label>
                        <input type="number" name="default_days_per_year" x-model="defaultDays" min="0" max="365" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_paid" value="1" x-model="isPaid" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Paid</span>
                        </label>
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

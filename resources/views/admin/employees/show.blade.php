@extends('layouts.admin')

@section('title', 'Employee Details')
@section('page-title', 'Employee: ' . $employee->name)

@section('content')
<div x-data="{ showAccessModal: false }">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $employee->name }}</h3>
                <p class="text-sm text-gray-500">{{ $employee->employee_code }}</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $employee->employment_status_color }}">
                        {{ $employee->employment_status_label }}
                    </span>
                    @if($employee->is_linked)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-user-check mr-1"></i> Has Login
                    </span>
                    @endif
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-medium">{{ $employee->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $employee->email ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">CNIC</span><span class="font-medium">{{ $employee->cnic ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Gender</span><span class="font-medium">{{ $employee->gender ? ucfirst($employee->gender) : '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Date of Birth</span><span class="font-medium">{{ optional($employee->date_of_birth)->format('d-m-Y') ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Emergency Contact</span><span class="font-medium">{{ $employee->emergency_contact_name ?? '-' }} {{ $employee->emergency_contact_phone ? '('.$employee->emergency_contact_phone.')' : '' }}</span></div>
                </div>
                @if($employee->address)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Address</p>
                    <p class="text-sm text-gray-900">{{ $employee->address }}</p>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex flex-wrap gap-2">
                @if(auth()->user()->hasPermission('employees', 'edit'))
                <a href="{{ route('admin.employees.edit', $employee) }}"
                    class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                @endif
                <a href="{{ route('admin.employees.index') }}"
                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>

            @if(!$employee->is_linked && auth()->user()->isAdmin())
            <div class="px-6 pb-6">
                <button type="button" @click="showAccessModal = true"
                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-key mr-1"></i> Grant System Access
                </button>
                <p class="text-xs text-gray-400 mt-2 text-center">This employee doesn't have a software login yet.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Column -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900"><i class="fas fa-briefcase text-gray-400 mr-2"></i> Employment Details</h4>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Department</span><span class="font-medium">{{ $employee->department->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Designation</span><span class="font-medium">{{ $employee->designation ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Employment Type</span><span class="font-medium">{{ $employee->employment_type_label }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Reporting Manager</span><span class="font-medium">{{ $employee->reportingManager->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Date of Joining</span><span class="font-medium">{{ optional($employee->date_of_joining)->format('d-m-Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Date of Leaving</span><span class="font-medium">{{ optional($employee->date_of_leaving)->format('d-m-Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Source</span><span class="font-medium">{{ $employee->source === 'auto_software_user' ? 'Auto (software login)' : 'Manually added' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Approved By</span><span class="font-medium">{{ $employee->approvedBy->name ?? '-' }}</span></div>
            </div>
            @if($employee->admin_note)
            <div class="px-6 pb-6">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Notes</p>
                    <p class="text-sm text-gray-900">{{ $employee->admin_note }}</p>
                </div>
            </div>
            @endif
        </div>

        @if($employee->is_linked)
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900"><i class="fas fa-user-shield text-gray-400 mr-2"></i> System Login</h4>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Login Email</span><span class="font-medium">{{ $employee->user->email ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Role</span><span class="font-medium">{{ $employee->user ? ucfirst(str_replace('_',' ',$employee->user->role)) : '-' }}</span></div>
            </div>
        </div>
        @endif

        @if($employee->subordinates->count() > 0)
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900"><i class="fas fa-sitemap text-gray-400 mr-2"></i> Direct Reports <span class="text-sm font-normal text-gray-500">({{ $employee->subordinates->count() }})</span></h4>
            </div>
            <div class="p-6 space-y-2">
                @foreach($employee->subordinates as $subordinate)
                <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                    <a href="{{ route('admin.employees.show', $subordinate) }}" class="text-blue-600 hover:underline">{{ $subordinate->name }}</a>
                    <span class="text-xs text-gray-500">{{ $subordinate->designation ?? $subordinate->employee_code }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@if(!$employee->is_linked && auth()->user()->isAdmin())
<!-- Grant System Access Modal -->
<div x-show="showAccessModal" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showAccessModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-key text-green-600 mr-2"></i> Grant System Access
                </h3>
                <button @click="showAccessModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4">Creates a login for {{ $employee->name }} and links it to this employee record.</p>
            <form action="{{ route('admin.employees.grant-access', $employee) }}" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Login Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ $employee->email }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="manager">Manager</option>
                            <option value="accountant">Accountant</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-check mr-1"></i> Create Login
                        </button>
                        <button type="button" @click="showAccessModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
</div>
@endsection

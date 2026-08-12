@extends('layouts.admin')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-users text-gray-400 mr-2"></i> All Employees
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $employees->count() }} total</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.departments.index') }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-sitemap mr-1"></i> Departments
            </a>
            @if(auth()->user()->hasPermission('employees', 'create'))
            <a href="{{ route('admin.employees.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Add Employee
            </a>
            @endif
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="employeesTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Code</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Name</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Department</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Designation</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Login</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($employees as $employee)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $employee->employee_code }}</code>
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $employee->name }}</span>
                            @if($employee->source === 'auto_software_user')
                                <span class="ml-1 text-xs text-blue-500" title="Auto-added from software login"><i class="fas fa-magic"></i></span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">{{ $employee->department->name ?? '-' }}</td>
                        <td class="py-3 px-2 text-sm text-gray-600">{{ $employee->designation ?? '-' }}</td>
                        <td class="py-3 px-2 text-center">
                            @if($employee->is_linked)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-user-check mr-1"></i> Has Login
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    No Login
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->employment_status_color }}">
                                {{ $employee->employment_status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.employees.show', $employee) }}"
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                @if(auth()->user()->hasPermission('employees', 'edit'))
                                <a href="{{ route('admin.employees.edit', $employee) }}"
                                   class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('employees', 'delete'))
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Remove this employee record?')"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                @endif
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
        if (document.getElementById('employeesTable')) {
            new DataTable('#employeesTable', {
                pageLength: 25,
                responsive: true,
                order: [[1, 'asc']],
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

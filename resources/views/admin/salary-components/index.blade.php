@extends('layouts.admin')

@section('title', 'Salary Structures')
@section('page-title', 'Salary Structures')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-money-check-alt text-gray-400 mr-2"></i> Salary Structures</span>
            <span class="ml-2 text-sm text-gray-500">{{ $employees->count() }} active employees</span>
        </div>
        <a href="{{ route('admin.payroll-runs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
            <i class="fas fa-money-bill-wave mr-1"></i> Payroll Runs
        </a>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Employee</th>
                        <th class="text-left py-3 px-2">Designation</th>
                        <th class="text-right py-3 px-2">Basic Pay</th>
                        <th class="text-right py-3 px-2">Allowances</th>
                        <th class="text-right py-3 px-2">Deductions</th>
                        <th class="text-left py-3 px-2">Effective From</th>
                        <th class="text-center py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $employee->name }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $employee->designation ?? '-' }}</td>
                        @if($employee->current_structure)
                            <td class="py-3 px-2 text-right">{{ number_format($employee->current_structure->basic_pay, 2) }}</td>
                            <td class="py-3 px-2 text-right text-green-600">{{ number_format($employee->current_structure->total_allowances, 2) }}</td>
                            <td class="py-3 px-2 text-right text-red-600">{{ number_format($employee->current_structure->total_deductions, 2) }}</td>
                            <td class="py-3 px-2 text-gray-600">{{ $employee->current_structure->effective_from->format('d M Y') }}</td>
                        @else
                            <td class="py-3 px-2 text-right text-gray-400" colspan="3">No salary structure set</td>
                            <td class="py-3 px-2 text-gray-400">-</td>
                        @endif
                        <td class="py-3 px-2 text-center">
                            <a href="{{ route('admin.salary-components.show', $employee) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-cog text-sm"></i> Manage
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">No active employees.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

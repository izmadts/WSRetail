@extends('layouts.admin')

@section('title', 'Payroll Runs')
@section('page-title', 'Payroll Runs')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-money-bill-wave text-gray-400 mr-2"></i> Payroll Runs</span>
            <span class="ml-2 text-sm text-gray-500">{{ $payrollRuns->count() }} total</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.salary-components.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-money-check-alt mr-1"></i> Salary Structures
            </a>
            @if(auth()->user()->hasPermission('payroll', 'create'))
            <a href="{{ route('admin.payroll-runs.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Run Payroll
            </a>
            @endif
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Period</th>
                        <th class="text-center py-3 px-2">Employees</th>
                        <th class="text-right py-3 px-2">Total Amount</th>
                        <th class="text-center py-3 px-2">Status</th>
                        <th class="text-left py-3 px-2">Processed By</th>
                        <th class="text-center py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payrollRuns as $run)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $run->month_name }} {{ $run->year }}</td>
                        <td class="py-3 px-2 text-center">{{ $run->payslips_count }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($run->total_amount, 2) }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $run->status_color }}">
                                {{ $run->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-gray-600">{{ $run->processedBy->name ?? '-' }}</td>
                        <td class="py-3 px-2 text-center">
                            <a href="{{ route('admin.payroll-runs.show', $run) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-6 text-center text-gray-400">No payroll runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

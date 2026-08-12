@extends('layouts.agent')

@section('title', 'Payslips')
@section('page-title', 'Payslips')

@section('content')
@if(!$employee)
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4">
    No employee record was found for your account - contact an admin.
</div>
@else
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <span class="text-sm font-medium text-gray-700"><i class="fas fa-file-invoice-dollar text-gray-400 mr-2"></i> My Payslips</span>
    </div>
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Period</th>
                        <th class="text-right py-3 px-2">Basic</th>
                        <th class="text-right py-3 px-2">Allowances</th>
                        <th class="text-right py-3 px-2">Overtime</th>
                        <th class="text-right py-3 px-2">Deductions</th>
                        <th class="text-right py-3 px-2">Net Pay</th>
                        <th class="text-right py-3 px-2">Paid</th>
                        <th class="text-center py-3 px-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payslips as $slip)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $slip->payrollRun->month_name ?? '-' }} {{ $slip->payrollRun->year ?? '' }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($slip->basic_pay, 2) }}</td>
                        <td class="py-3 px-2 text-right text-green-600">{{ number_format($slip->total_allowances, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($slip->overtime_amount, 2) }}</td>
                        <td class="py-3 px-2 text-right text-red-600">{{ number_format($slip->total_deductions, 2) }}</td>
                        <td class="py-3 px-2 text-right font-medium">{{ number_format($slip->net_pay, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($slip->payments->sum('amount'), 2) }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $slip->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $slip->is_paid ? 'Paid' : 'Pending' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-6 text-center text-gray-400">No payslips yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

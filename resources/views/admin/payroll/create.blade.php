@extends('layouts.admin')

@section('title', 'Run Payroll')
@section('page-title', 'Run Payroll')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('admin.payroll-runs.create') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="px-3 py-2 border border-gray-300 rounded-lg w-28">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Month</label>
                <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-sync mr-1"></i> Preview
            </button>
        </form>
    </div>
</div>

@if($preview->isEmpty())
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4">
    No active employees have a salary structure set for this period yet. Set one under Salary Structures first.
</div>
@else
<form method="POST" action="{{ route('admin.payroll-runs.store') }}">
    @csrf
    <input type="hidden" name="year" value="{{ $year }}">
    <input type="hidden" name="month" value="{{ $month }}">

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-money-bill-wave text-gray-400 mr-2"></i> Preview - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </span>
            <span class="text-sm text-gray-500">{{ $preview->count() }} employees</span>
        </div>
        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-2">Employee</th>
                            <th class="text-right py-3 px-2">Basic</th>
                            <th class="text-right py-3 px-2">Allowances</th>
                            <th class="text-right py-3 px-2">Deductions</th>
                            <th class="text-right py-3 px-2 w-32">Overtime</th>
                            <th class="text-right py-3 px-2">Net Pay</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($preview as $row)
                        @php
                            $c = $row['component'];
                            $net = $c->basic_pay + $c->total_allowances - $c->total_deductions;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-2 font-medium text-gray-900">{{ $row['employee']->name }}</td>
                            <td class="py-3 px-2 text-right">{{ number_format($c->basic_pay, 2) }}</td>
                            <td class="py-3 px-2 text-right text-green-600">{{ number_format($c->total_allowances, 2) }}</td>
                            <td class="py-3 px-2 text-right text-red-600">{{ number_format($c->total_deductions, 2) }}</td>
                            <td class="py-3 px-2 text-right">
                                <input type="number" step="0.01" min="0" name="overtime[{{ $row['employee']->id }}]" value="0"
                                    class="w-24 px-2 py-1 border border-gray-300 rounded text-right">
                            </td>
                            <td class="py-3 px-2 text-right font-medium">{{ number_format($net, 2) }} <span class="text-xs text-gray-400">+OT</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex items-center gap-3">
            <button type="submit" onclick="return confirm('Process payroll for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}? This will post real ledger entries and cannot be undone.')"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-check mr-1"></i> Process Payroll
            </button>
            <a href="{{ route('admin.payroll-runs.index') }}" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                Cancel
            </a>
        </div>
    </div>
</form>
@endif
@endsection

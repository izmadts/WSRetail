@extends('layouts.admin')

@section('title', 'Payroll - ' . $payrollRun->month_name . ' ' . $payrollRun->year)
@section('page-title', 'Payroll - ' . $payrollRun->month_name . ' ' . $payrollRun->year)

@section('content')
<div x-data="{
    showPayModal: false,
    payslipId: null,
    dueAmount: 0,
    openPay(id, due) { this.payslipId = id; this.dueAmount = due; this.showPayModal = true; },
}">
<div class="bg-white rounded-xl shadow-card overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-money-bill-wave text-gray-400 mr-2"></i> {{ $payrollRun->month_name }} {{ $payrollRun->year }}</span>
            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payrollRun->status_color }}">{{ $payrollRun->status_label }}</span>
        </div>
        <a href="{{ route('admin.payroll-runs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-1"></i> All Runs
        </a>
    </div>
    <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><p class="text-sm text-gray-500">Total Payslips</p><p class="text-xl font-bold text-gray-900">{{ $payslips->count() }}</p></div>
        <div><p class="text-sm text-gray-500">Total Net Amount</p><p class="text-xl font-bold text-gray-900">{{ number_format($payrollRun->total_amount, 2) }}</p></div>
        <div><p class="text-sm text-gray-500">Processed By</p><p class="text-xl font-bold text-gray-900">{{ $payrollRun->processedBy->name ?? '-' }}</p></div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Employee</th>
                        <th class="text-right py-3 px-2">Basic</th>
                        <th class="text-right py-3 px-2">Allowances</th>
                        <th class="text-right py-3 px-2">Overtime</th>
                        <th class="text-right py-3 px-2">Deductions</th>
                        <th class="text-right py-3 px-2">Net Pay</th>
                        <th class="text-right py-3 px-2">Paid</th>
                        <th class="text-center py-3 px-2">Status</th>
                        <th class="text-center py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payslips as $slip)
                    @php $paid = $slip->payments->sum('amount'); $due = round($slip->net_pay - $paid, 2); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $slip->employee->name ?? '-' }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($slip->basic_pay, 2) }}</td>
                        <td class="py-3 px-2 text-right text-green-600">{{ number_format($slip->total_allowances, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($slip->overtime_amount, 2) }}</td>
                        <td class="py-3 px-2 text-right text-red-600">{{ number_format($slip->total_deductions, 2) }}</td>
                        <td class="py-3 px-2 text-right font-medium">{{ number_format($slip->net_pay, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($paid, 2) }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $slip->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $slip->is_paid ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid') }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            @if(!$slip->is_paid && auth()->user()->hasPermission('payroll', 'edit'))
                            <button type="button" @click="openPay({{ $slip->id }}, {{ $due }})" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors duration-200">
                                <i class="fas fa-hand-holding-usd mr-1"></i> Pay
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-6 text-center text-gray-400">No payslips in this run.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pay Modal -->
<div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showPayModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Record Salary Payment</h3>
                <button @click="showPayModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="'{{ url('admin/payroll-runs/payslips') }}/' + payslipId + '/pay'" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" :max="dueAmount" :value="dueAmount" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">Due: <span x-text="dueAmount"></span></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference No.</label>
                        <input type="text" name="reference_no" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-save mr-1"></i> Record Payment
                        </button>
                        <button type="button" @click="showPayModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
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

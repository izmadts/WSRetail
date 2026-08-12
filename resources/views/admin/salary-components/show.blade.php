@extends('layouts.admin')

@section('title', 'Salary Structure - ' . $employee->name)
@section('page-title', 'Salary Structure')

@section('content')
<div x-data="{ showModal: false }">
<div class="bg-white rounded-xl shadow-card overflow-hidden mb-6">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-user text-gray-400 mr-2"></i> {{ $employee->name }}</span>
            <span class="ml-2 text-sm text-gray-500">{{ $employee->employee_code }} &middot; {{ $employee->designation ?? '-' }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.salary-components.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-1"></i> All Employees
            </a>
            @if(auth()->user()->hasPermission('payroll', 'create'))
            <button type="button" @click="showModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> New Structure
            </button>
            @endif
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Effective From</th>
                        <th class="text-right py-3 px-2">Basic Pay</th>
                        <th class="text-right py-3 px-2">House Rent</th>
                        <th class="text-right py-3 px-2">Medical</th>
                        <th class="text-right py-3 px-2">Fuel</th>
                        <th class="text-right py-3 px-2">Other Allow.</th>
                        <th class="text-right py-3 px-2">Tax Ded.</th>
                        <th class="text-right py-3 px-2">Other Ded.</th>
                        <th class="text-right py-3 px-2">Net (before OT)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($history as $i => $s)
                    <tr class="hover:bg-gray-50 {{ $i === 0 ? 'bg-blue-50/50' : '' }}">
                        <td class="py-3 px-2 font-medium text-gray-900">
                            {{ $s->effective_from->format('d M Y') }}
                            @if($i === 0)<span class="ml-1 text-xs text-blue-600">(current)</span>@endif
                        </td>
                        <td class="py-3 px-2 text-right">{{ number_format($s->basic_pay, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($s->house_rent_allowance, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($s->medical_allowance, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($s->fuel_allowance, 2) }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($s->other_allowance, 2) }}</td>
                        <td class="py-3 px-2 text-right text-red-600">{{ number_format($s->tax_deduction, 2) }}</td>
                        <td class="py-3 px-2 text-right text-red-600">{{ number_format($s->other_deduction, 2) }}</td>
                        <td class="py-3 px-2 text-right font-medium">{{ number_format($s->basic_pay + $s->total_allowances - $s->total_deductions, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-6 text-center text-gray-400">No salary structure set for this employee yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Salary Structure Modal -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">New Salary Structure</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.salary-components.store', $employee) }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Basic Pay <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="basic_pay" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">House Rent Allowance</label>
                        <input type="number" step="0.01" min="0" name="house_rent_allowance" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medical Allowance</label>
                        <input type="number" step="0.01" min="0" name="medical_allowance" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Allowance</label>
                        <input type="number" step="0.01" min="0" name="fuel_allowance" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Other Allowance</label>
                        <input type="number" step="0.01" min="0" name="other_allowance" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Deduction</label>
                        <input type="number" step="0.01" min="0" name="tax_deduction" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Other Deduction</label>
                        <input type="number" step="0.01" min="0" name="other_deduction" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Effective From <span class="text-red-500">*</span></label>
                        <input type="date" name="effective_from" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

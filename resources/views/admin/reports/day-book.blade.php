@extends('layouts.admin')

@section('title', 'Day Book')
@section('page-title', 'Day Book (General Journal)')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-card p-4 flex flex-wrap items-end justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $from_date }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $to_date }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> View
                </button>
            </div>
        </form>
        <div class="pt-6">
            <a href="{{ route('admin.reports.day-book-pdf', request()->only(['from_date', 'to_date'])) }}" target="_blank" class="inline-flex items-center justify-center px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 whitespace-nowrap">
                <i class="fas fa-file-pdf mr-1"></i> Print / PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Debits</p>
            <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($total_debit, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Credits</p>
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($total_credit, 2) }}</p>
        </div>
    </div>
    @if(abs($total_debit - $total_credit) > 0.01)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
        <i class="fas fa-exclamation-triangle mr-1"></i> Out of balance by Rs. {{ number_format(abs($total_debit - $total_credit), 2) }} - this should never happen; every voucher posts equal debits and credits.
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="dayBookTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Date</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Voucher</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Account</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Description</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Debit</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 px-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($entry->entry_date)->format('d-M-Y') }}</td>
                            <td class="py-2 px-2 text-gray-500">{{ ucfirst(str_replace('_', ' ', $entry->reference_type)) }} #{{ $entry->reference_id }}</td>
                            <td class="py-2 px-2">{{ $entry->account->code }} - {{ $entry->account->name }}</td>
                            <td class="py-2 px-2">{{ $entry->description }}</td>
                            <td class="py-2 px-2 text-right text-red-600 font-medium">{{ $entry->type === 'debit' ? 'Rs. ' . number_format($entry->amount, 2) : '' }}</td>
                            <td class="py-2 px-2 text-right text-green-600 font-medium">{{ $entry->type === 'credit' ? 'Rs. ' . number_format($entry->amount, 2) : '' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-400">No transactions posted in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="4" class="text-right py-2 px-2">Totals:</td>
                            <td class="text-right py-2 px-2 text-red-600">Rs. {{ number_format($total_debit, 2) }}</td>
                            <td class="text-right py-2 px-2 text-green-600">Rs. {{ number_format($total_credit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

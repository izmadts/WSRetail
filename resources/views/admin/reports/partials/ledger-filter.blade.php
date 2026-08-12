{{-- Shared date-range filter + Print PDF button, used by every ledger-style report. Expects $action (form target URL) and $pdfUrl. --}}
<div class="bg-white rounded-xl shadow-card p-4 flex flex-wrap items-end justify-between gap-4">
    <form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="pt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </div>
        @if(request('from_date') || request('to_date'))
        <div class="pt-6">
            <a href="{{ $action }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                <i class="fas fa-undo mr-1"></i> Reset
            </a>
        </div>
        @endif
    </form>
    <div class="pt-6">
        <a href="{{ $pdfUrl }}" target="_blank" class="inline-flex items-center justify-center px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 whitespace-nowrap">
            <i class="fas fa-file-pdf mr-1"></i> Print / PDF
        </a>
    </div>
</div>

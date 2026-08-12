@extends('layouts.agent')
@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold"><i class="fas fa-file-invoice text-gray-400 mr-2"></i> Sales Report</h4>
        <a href="{{ route('agent.reports.index') }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Back</a>
    </div>
    <div class="p-6">
        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div><label class="block text-xs font-medium text-gray-700">From</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"></div>
            <div><label class="block text-xs font-medium text-gray-700">To</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"></div>
            <div><label class="block text-xs font-medium text-gray-700">Status</label><select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"><option value="">All</option><option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option><option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>Confirmed</option><option value="partial" {{ request('status')=='partial'?'selected':'' }}>Partial</option><option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option></select></div>
            <div class="flex items-end"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700"><i class="fas fa-filter"></i> Filter</button><a href="{{ route('agent.reports.sales') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Reset</a></div>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="p-3 bg-blue-50 rounded-lg text-center"><p class="text-xs text-gray-500">Total Sales</p><p class="text-lg font-bold text-blue-600">Rs. {{ number_format($summary['total_amount']??0,2) }}</p></div>
            <div class="p-3 bg-green-50 rounded-lg text-center"><p class="text-xs text-gray-500">Total Paid</p><p class="text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid']??0,2) }}</p></div>
            <div class="p-3 bg-red-50 rounded-lg text-center"><p class="text-xs text-gray-500">Total Due</p><p class="text-lg font-bold text-red-600">Rs. {{ number_format($summary['total_due']??0,2) }}</p></div>
            <div class="p-3 bg-gray-50 rounded-lg text-center"><p class="text-xs text-gray-500">Orders</p><p class="text-lg font-bold text-gray-700">{{ $summary['count']??0 }}</p></div>
        </div>
        <table class="w-full text-sm" id="salesReportTable"><thead><tr class="border-b"><th class="text-left py-2 px-2">Invoice</th><th class="text-left py-2 px-2">Customer</th><th class="text-left py-2 px-2">Date</th><th class="text-right py-2 px-2">Total</th><th class="text-right py-2 px-2">Paid</th><th class="text-right py-2 px-2">Due</th><th class="text-center py-2 px-2">Status</th></tr></thead><tbody class="divide-y divide-gray-100">@foreach($sales as $sale)<tr><td class="py-2 px-2">{{ $sale->invoice_no }}</td><td class="py-2 px-2">{{ $sale->customer->name??'N/A' }}</td><td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td><td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount,2) }}</td><td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($sale->paid_amount,2) }}</td><td class="py-2 px-2 text-right text-red-600">Rs. {{ number_format($sale->due_amount,2) }}</td><td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></td></tr>@endforeach</tbody></table>
        <div class="mt-4">{{ $sales->links() }}</div>
    </div>
</div>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',function(){if(document.getElementById('salesReportTable')){new DataTable('#salesReportTable',{pageLength:25,order:[[2,'desc']]});}});</script>@endpush
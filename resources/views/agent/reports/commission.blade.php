@extends('layouts.agent')
@section('title', 'Commission Report')
@section('page-title', 'Commission Report')
@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h4 class="text-lg font-semibold"><i class="fas fa-coins text-gray-400 mr-2"></i> Commission Report</h4>
        <a href="{{ route('agent.reports.index') }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Back</a>
    </div>
    <div class="p-6">
        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div><label class="block text-xs font-medium text-gray-700">From</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"></div>
            <div><label class="block text-xs font-medium text-gray-700">To</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"></div>
            <div><label class="block text-xs font-medium text-gray-700">Type</label><select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"><option value="all">All</option><option value="sale" {{ request('type')=='sale'?'selected':'' }}>Sale Commission</option><option value="new_customer_bonus" {{ request('type')=='new_customer_bonus'?'selected':'' }}>New Customer Bonus</option><option value="target_bonus" {{ request('type')=='target_bonus'?'selected':'' }}>Target Bonus</option><option value="recovery_bonus" {{ request('type')=='recovery_bonus'?'selected':'' }}>Recovery Bonus</option></select></div>
            <div class="flex items-end"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700"><i class="fas fa-filter"></i> Filter</button><a href="{{ route('agent.reports.commission') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Reset</a></div>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="p-3 bg-blue-50 rounded-lg text-center"><p class="text-xs text-gray-500">Total Earned</p><p class="text-lg font-bold text-blue-600">Rs. {{ number_format($summary['total_earned']??0,2) }}</p></div>
            <div class="p-3 bg-green-50 rounded-lg text-center"><p class="text-xs text-gray-500">Paid</p><p class="text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid']??0,2) }}</p></div>
            <div class="p-3 bg-red-50 rounded-lg text-center"><p class="text-xs text-gray-500">Due</p><p class="text-lg font-bold text-red-600">Rs. {{ number_format($summary['total_due']??0,2) }}</p></div>
            <div class="p-3 bg-gray-50 rounded-lg text-center"><p class="text-xs text-gray-500">Records</p><p class="text-lg font-bold text-gray-700">{{ $summary['count']??0 }}</p></div>
        </div>
        <table class="w-full text-sm" id="commissionReportTable"><thead><tr class="border-b"><th class="text-left py-2 px-2">Date</th><th class="text-left py-2 px-2">Type</th><th class="text-left py-2 px-2">Description</th><th class="text-right py-2 px-2">Amount</th><th class="text-center py-2 px-2">Status</th></tr></thead><tbody class="divide-y divide-gray-100">@foreach($commissions as $log)<tr><td class="py-2 px-2">{{ $log->created_at->format('d-m-Y H:i') }}</td><td class="py-2 px-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $log->reference_type=='sale'?'bg-blue-100 text-blue-800':($log->reference_type=='new_customer_bonus'?'bg-green-100 text-green-800':($log->reference_type=='target_bonus'?'bg-purple-100 text-purple-800':'bg-yellow-100 text-yellow-800')) }}">{{ $log->type_label }}</span></td><td class="py-2 px-2">{{ $log->description??'-' }}</td><td class="py-2 px-2 text-right font-medium text-purple-600">+ Rs. {{ number_format($log->amount,2) }}</td><td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $log->is_paid?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800' }}">{{ $log->is_paid?'Paid':'Pending' }}</span></td></tr>@endforeach</tbody></table>
        <div class="mt-4">{{ $commissions->links() }}</div>
    </div>
</div>
@endsection
@push('scripts')<script>document.addEventListener('DOMContentLoaded',function(){if(document.getElementById('commissionReportTable')){new DataTable('#commissionReportTable',{pageLength:25,order:[[0,'desc']]});}});</script>@endpush
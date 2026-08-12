@extends('layouts.agent')
@section('title', 'My Commissions')
@section('page-title', 'My Commissions')
@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200"><h4 class="text-lg font-semibold">Commission Summary</h4></div>
    <div class="p-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-blue-50 rounded-lg"><p class="text-sm text-gray-500">Total Earned</p><p class="text-xl font-bold text-blue-600">Rs. {{ number_format($totalEarned??0,2) }}</p></div>
        <div class="text-center p-4 bg-green-50 rounded-lg"><p class="text-sm text-gray-500">Paid</p><p class="text-xl font-bold text-green-600">Rs. {{ number_format($totalPaid??0,2) }}</p></div>
        <div class="text-center p-4 bg-red-50 rounded-lg"><p class="text-sm text-gray-500">Due</p><p class="text-xl font-bold text-red-600">Rs. {{ number_format($totalDue??0,2) }}</p></div>
        <div class="text-center p-4 bg-purple-50 rounded-lg"><p class="text-sm text-gray-500">This Month</p><p class="text-xl font-bold text-purple-600">Rs. {{ number_format($monthEarned??0,2) }}</p></div>
    </div>
</div>
<div class="mt-6 bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between"><h4 class="text-lg font-semibold"><i class="fas fa-list text-gray-400 mr-2"></i> Commission Logs</h4><div class="text-sm text-gray-500">Total: {{ $commissions->total() }}</div></div>
    <div class="p-6">@if($commissions->count()>0)<table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2 px-2">Date</th><th class="text-left py-2 px-2">Type</th><th class="text-left py-2 px-2">Description</th><th class="text-right py-2 px-2">Amount</th><th class="text-center py-2 px-2">Status</th></tr></thead><tbody class="divide-y divide-gray-100">@foreach($commissions as $log)<tr><td class="py-2 px-2">{{ $log->created_at->format('d-m-Y H:i') }}</td><td class="py-2 px-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $log->reference_type=='sale'?'bg-blue-100 text-blue-800':($log->reference_type=='new_customer_bonus'?'bg-green-100 text-green-800':($log->reference_type=='target_bonus'?'bg-purple-100 text-purple-800':'bg-yellow-100 text-yellow-800')) }}">{{ $log->type_label }}</span></td><td class="py-2 px-2">{{ $log->description??'-' }}</td><td class="py-2 px-2 text-right font-medium text-purple-600">+ Rs. {{ number_format($log->amount,2) }}</td><td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $log->is_paid?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800' }}">{{ $log->is_paid?'Paid':'Pending' }}</span></td></tr>@endforeach</tbody></table><div class="mt-4">{{ $commissions->links() }}</div>@else<p class="text-center text-gray-500 py-8">No commission records.</p>@endif</div>
</div>
@endsection
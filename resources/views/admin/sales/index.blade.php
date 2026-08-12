@extends('layouts.admin') 

@section('title', 'Sales')
@section('page-title', 'Sales Management')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div><span class="text-sm font-medium text-gray-700"><i class="fas fa-shopping-cart text-gray-400 mr-2"></i> All Sales</span><span class="ml-2 text-sm text-gray-500">{{ $sales->count() }} total</span></div>
        <a href="{{ route('admin.sales.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200"><i class="fas fa-plus mr-1"></i> New Sale</a>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="salesTable">
                <thead><tr class="border-b border-gray-200"><th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Invoice</th><th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Customer</th><th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Total</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Paid</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Due</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $sale->invoice_no }}</code></td>
                        <td class="py-3 px-2"><span class="font-medium text-gray-900">{{ $sale->customer->name ?? 'N/A' }}</span></td>
                        <td class="py-3 px-2 text-sm text-gray-600">{{ $sale->sale_date->format('d-m-Y') }}</td>
                        <td class="py-3 px-2 text-right font-medium text-gray-900">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                        <td class="py-3 px-2 text-right text-green-600">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="py-3 px-2 text-right text-red-600">Rs. {{ number_format($sale->due_amount, 2) }}</td>
                        <td class="py-3 px-2 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.sales.show', $sale) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"><i class="fas fa-eye text-sm"></i></a>
                                @if($sale->status != 'paid' && $sale->status != 'cancelled')
                                <a href="{{ route('admin.sales.edit', $sale) }}" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg"><i class="fas fa-edit text-sm"></i></a>
                                <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST" class="inline">@csrf @method('DELETE')<button type="submit" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"><i class="fas fa-trash text-sm"></i></button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('salesTable')) {
            new DataTable('#salesTable', { pageLength: 25, responsive: true, order: [[0, 'desc']], language: { search: "Search:", lengthMenu: "Show _MENU_ entries", info: "Showing _START_ to _END_ of _TOTAL_ entries" } });
        }
    });
</script>
@endpush
@extends('layouts.admin')

@section('title', 'Purchase Returns')
@section('page-title', 'Purchase Returns Management')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-undo-alt text-gray-400 mr-2"></i> All Purchase Returns
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $returns->count() }} total</span>
        </div>
        <a href="{{ route('admin.purchase-returns.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> New Return
        </a>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="returnsTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Return No</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">PO #</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Supplier</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Amount</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Refund Method</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($returns as $return)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $return->return_no }}</code>
                        </td>
                        <td class="py-3 px-2">
                            <a href="{{ route('admin.purchases.show', $return->purchase) }}" class="text-blue-600 hover:underline text-sm">
                                {{ $return->purchase->invoice_no ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $return->supplier->name ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $return->return_date->format('d-m-Y') }}
                        </td>
                        <td class="py-3 px-2 text-right font-medium text-red-600">
                            Rs. {{ number_format($return->total_amount, 2) }}
                        </td>
                        <td class="py-3 px-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $return->refund_method_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.purchase-returns.show', $return) }}" 
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <form action="{{ route('admin.purchase-returns.destroy', $return) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure? This will reverse the return.')" 
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
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
        if (document.getElementById('returnsTable')) {
            new DataTable('#returnsTable', {
                pageLength: 25,
                responsive: true,
                order: [[3, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        }
    });
</script>
@endpush
@extends('layouts.admin')

@section('title', 'Purchases')
@section('page-title', 'All Purchases')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-shopping-cart text-gray-400 mr-2"></i> All Purchases
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $purchases->count() }} total</span>
        </div>
        <a href="{{ route('admin.purchases.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> New Purchase
        </a>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="purchasesTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Invoice</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Supplier</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Items</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Total</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Paid</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Due</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchases as $purchase)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $purchase->invoice_no }}</code>
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $purchase->items->sum('quantity') }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $purchase->purchase_date->format('d-m-Y') }}
                        </td>
                        <td class="py-3 px-2 text-right font-medium text-gray-900">
                            Rs. {{ number_format($purchase->total_amount, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right text-green-600">
                            Rs. {{ number_format($purchase->paid_amount, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right text-red-600">
                            Rs. {{ number_format($purchase->due_amount, 2) }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            @php
                            $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'ordered' => 'bg-blue-100 text-blue-800',
                            'received' => 'bg-yellow-100 text-yellow-800',
                            'partial' => 'bg-orange-100 text-orange-800',
                            'paid' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$purchase->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.purchases.show', $purchase) }}"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                @if($purchase->status != 'paid' && $purchase->status != 'cancelled')
                                <a href="{{ route('admin.purchases.edit', $purchase) }}"
                                    class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <form action="{{ route('admin.purchases.destroy', $purchase) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
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
        if (document.getElementById('purchasesTable')) {
            new DataTable('#purchasesTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [3, 'desc']
                ],
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
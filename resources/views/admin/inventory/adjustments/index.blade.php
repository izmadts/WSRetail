@extends('layouts.admin')

@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-sliders-h text-gray-400 mr-2"></i> All Adjustments
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $adjustments->count() }} total</span>
        </div>
        <a href="{{ route('admin.inventory.adjustments.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> New Adjustment
        </a>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="adjustmentsTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Date</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Product</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Type</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Quantity</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Before</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">After</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Reason</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">By</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($adjustments as $adjustment)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ $adjustment->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $adjustment->product->name ?? 'Deleted' }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $adjustment->type == 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $adjustment->type_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-right font-medium">
                            {{ number_format($adjustment->quantity, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right text-gray-600">
                            {{ number_format($adjustment->stock_before, 2) }}
                        </td>
                        <td class="py-3 px-2 text-right font-medium {{ $adjustment->stock_after > $adjustment->stock_before ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($adjustment->stock_after, 2) }}
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">
                            {{ Str::limit($adjustment->reason ?? '-', 30) }}
                        </td>
                        <td class="py-3 px-2 text-center text-sm text-gray-600">
                            {{ $adjustment->createdBy->name ?? '-' }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            <form action="{{ route('admin.inventory.adjustments.destroy', $adjustment) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure?')" 
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
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
        if (document.getElementById('adjustmentsTable')) {
            new DataTable('#adjustmentsTable', {
                pageLength: 25,
                responsive: true,
                order: [[0, 'desc']],
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
@extends('layouts.admin')

@section('title', 'Sale Details')
@section('page-title', 'Sale: ' . $sale->invoice_no)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div><span class="text-sm font-medium text-gray-700"><i class="fas fa-file-invoice text-gray-400 mr-2"></i> Sale Details</span></div>
                <div class="flex flex-wrap gap-2">
                    @if($sale->status != 'paid' && $sale->status != 'cancelled')
                    <a href="{{ route('admin.sales.edit', $sale) }}" class="px-3 py-1.5 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700">Edit</a>
                    @endif
                    <a href="{{ route('admin.sales.index') }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300">Back</a>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-500">Invoice No</p><p class="font-medium text-gray-900">{{ $sale->invoice_no }}</p></div>
                <div><p class="text-sm text-gray-500">Customer</p><p class="font-medium text-gray-900">{{ $sale->customer->name ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-500">Agent</p><p class="font-medium text-gray-900">{{ $sale->agent->name ?? 'N/A' }}</p></div>
                <div><p class="text-sm text-gray-500">Sale Date</p><p class="font-medium text-gray-900">{{ $sale->sale_date->format('d-m-Y') }}</p></div>
                <div><p class="text-sm text-gray-500">Payment Term</p><p class="font-medium text-gray-900">{{ ucfirst($sale->payment_term) }}</p></div>
                <div><p class="text-sm text-gray-500">Status</p><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></div>
                <div><p class="text-sm text-gray-500">Source</p><p class="font-medium text-gray-900">
                    @if($sale->source === 'customer_app')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-mobile-alt mr-1"></i> Customer App{{ $sale->agent_id ? '' : ' - Direct/Wholesale' }}</span>
                    @else
                        {{ ucfirst($sale->source ?? 'web') }}
                    @endif
                </p></div>
                <div class="md:col-span-2"><p class="text-sm text-gray-500">Notes</p><p class="text-sm text-gray-900">{{ $sale->notes ?? '-' }}</p></div>
            </div>
        </div>
        
        <!-- Items -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-list text-gray-400 mr-2"></i> Items</h4></div>
            <div class="p-6 overflow-x-auto">
                <table class="w-full"><thead><tr class="border-b border-gray-200"><th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Product</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Qty</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Price</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Disc</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Tax</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sale->items as $item)
                        <tr><td class="py-2 px-2 font-medium">{{ $item->product->name ?? 'N/A' }}</td><td class="py-2 px-2 text-right">{{ number_format($item->quantity, 2) }}</td><td class="py-2 px-2 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td><td class="py-2 px-2 text-right">Rs. {{ number_format($item->discount, 2) }}</td><td class="py-2 px-2 text-right">Rs. {{ number_format($item->tax, 2) }}</td><td class="py-2 px-2 text-right font-medium">Rs. {{ number_format($item->total_price, 2) }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-medium"><tr><td colspan="5" class="text-right py-2 px-2">Sub Total:</td><td class="text-right py-2 px-2">Rs. {{ number_format($sale->sub_total, 2) }}</td></tr>
                    @if($sale->discount > 0)<tr><td colspan="5" class="text-right py-2 px-2">Discount:</td><td class="text-right py-2 px-2 text-red-600">- Rs. {{ number_format($sale->discount, 2) }}</td></tr>@endif
                    @if($sale->tax > 0)<tr><td colspan="5" class="text-right py-2 px-2">Tax:</td><td class="text-right py-2 px-2">Rs. {{ number_format($sale->tax, 2) }}</td></tr>@endif
                    @if($sale->shipping_cost > 0)<tr><td colspan="5" class="text-right py-2 px-2">Shipping:</td><td class="text-right py-2 px-2">Rs. {{ number_format($sale->shipping_cost, 2) }}</td></tr>@endif
                    @if($sale->commission_amount > 0)<tr><td colspan="5" class="text-right py-2 px-2 text-purple-600">Commission:</td><td class="text-right py-2 px-2 text-purple-600">Rs. {{ number_format($sale->commission_amount, 2) }}</td></tr>@endif
                    <tr class="text-lg"><td colspan="5" class="text-right py-2 px-2 font-bold">Grand Total:</td><td class="text-right py-2 px-2 font-bold text-blue-600">Rs. {{ number_format($sale->total_amount, 2) }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-credit-card text-gray-400 mr-2"></i> Payment Summary</h4></div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Amount</span><span class="font-medium text-gray-900">Rs. {{ number_format($sale->total_amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Paid Amount</span><span class="font-medium text-green-600">Rs. {{ number_format($sale->paid_amount, 2) }}</span></div>
                <div class="flex justify-between border-t border-gray-200 pt-3"><span class="text-sm font-medium text-gray-700">Due Amount</span><span class="font-bold {{ $sale->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">Rs. {{ number_format($sale->due_amount, 2) }}</span></div>
            </div>
        </div>
        
        @if($sale->status === 'draft')
        <div class="bg-white rounded-xl shadow-card overflow-hidden border-2 border-purple-200">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-hourglass-half text-purple-600 mr-2"></i> Pending Confirmation</h4></div>
            <div class="p-6 space-y-3">
                <p class="text-sm text-gray-500">This order hasn't been confirmed yet - stock and accounting are only committed once confirmed.</p>
                <div class="flex gap-2">
                    <form action="{{ route('admin.sales.confirm', $sale) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700"><i class="fas fa-check mr-1"></i> Confirm</button>
                    </form>
                    <form action="{{ route('admin.sales.reject', $sale) }}" method="POST" class="flex-1" onsubmit="return confirm('Reject this order?');">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700"><i class="fas fa-times mr-1"></i> Reject</button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($sale->due_amount > 0 && $sale->status != 'cancelled' && $sale->status != 'draft')
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-plus-circle text-green-600 mr-2"></i> Add Payment</h4></div>
            <div class="p-6" x-data="{ method: 'cash' }">
                <form action="{{ route('admin.sales.add-payment', $sale) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label><input type="number" step="0.01" name="amount" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" min="0.01" max="{{ $sale->due_amount }}" required><p class="text-xs text-gray-500 mt-1">Max: Rs. {{ number_format($sale->due_amount, 2) }}</p></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label><input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label><select name="payment_method" x-model="method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option><option value="credit_card">Credit Card</option></select></div>
                        <div x-show="method !== 'cash'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Bank Service Charge (Optional)
                                <x-help-tooltip>If the bank deducted a service charge for this transfer/cheque/card payment, enter it here - it'll be auto-recorded as a separate paid Expense (category "Bank Charges") dated the same day, so it shows up in the Expense list without any extra data entry.</x-help-tooltip>
                            </label>
                            <input type="number" step="0.01" name="bank_service_charge" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00">
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Reference No</label><input type="text" name="reference_no" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cheque no / Transaction ID"></div>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200"><i class="fas fa-check mr-1"></i> Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        
        @if($sale->payments->count() > 0)
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-history text-gray-400 mr-2"></i> Payment History</h4></div>
            <div class="p-6 space-y-3">
                @foreach($sale->payments as $payment)
                <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                    <div class="flex justify-between"><div><p class="font-medium text-gray-900">Rs. {{ number_format($payment->amount, 2) }}</p><p class="text-xs text-gray-500">{{ $payment->payment_date->format('d-m-Y') }}</p></div><div class="text-right"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>@if($payment->reference_no)<p class="text-xs text-gray-500 mt-1">{{ $payment->reference_no }}</p>@endif</div></div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

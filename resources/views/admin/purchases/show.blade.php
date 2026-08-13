@extends('layouts.admin')

@section('title', 'Purchase Details')
@section('page-title', 'Purchase: ' . $purchase->invoice_no)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Purchase Info -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Purchase Header -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-file-invoice text-gray-400 mr-2"></i> Purchase Details
                    </span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($purchase->status != 'paid' && $purchase->status != 'cancelled')
                    <a href="{{ route('admin.purchases.edit', $purchase) }}"
                        class="px-3 py-1.5 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors duration-200">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    @endif
                    <a href="{{ route('admin.purchases.index') }}"
                        class="px-3 py-1.5 bg-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Invoice No</p>
                        <p class="font-medium text-gray-900">{{ $purchase->invoice_no }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Supplier</p>
                        <p class="font-medium text-gray-900">{{ $purchase->supplier->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $purchase->supplier->phone ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Purchase Date</p>
                        <p class="font-medium text-gray-900">{{ $purchase->purchase_date->format('d-m-Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Payment Term</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($purchase->payment_term) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
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
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created By</p>
                        <p class="font-medium text-gray-900">{{ $purchase->createdBy->name ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($purchase->notes)
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Notes</p>
                    <p class="text-sm text-gray-900">{{ $purchase->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Purchase Items -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-gray-400 mr-2"></i> Items
                </h4>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Product</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Qty</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Price</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Discount</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Tax</th>
                                <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchase->items as $item)
                            <tr>
                                <td class="py-2 px-2">
                                    <span class="font-medium text-gray-900">{{ $item->product->name ?? 'N/A' }}{{ $item->variant ? ' (' . $item->variant->label . ')' : '' }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $item->product->code ?? '' }}</span>
                                </td>
                                <td class="py-2 px-2 text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($item->discount, 2) }}</td>
                                <td class="py-2 px-2 text-right">Rs. {{ number_format($item->tax, 2) }}</td>
                                <td class="py-2 px-2 text-right font-medium">Rs. {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-medium">
                            <tr>
                                <td colspan="5" class="text-right py-2 px-2">Sub Total:</td>
                                <td class="text-right py-2 px-2">Rs. {{ number_format($purchase->sub_total, 2) }}</td>
                            </tr>
                            @if($purchase->discount > 0)
                            <tr>
                                <td colspan="5" class="text-right py-2 px-2">Discount ({{ $purchase->discount_type }}):</td>
                                <td class="text-right py-2 px-2 text-red-600">- Rs. {{ number_format($purchase->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($purchase->tax > 0)
                            <tr>
                                <td colspan="5" class="text-right py-2 px-2">Tax:</td>
                                <td class="text-right py-2 px-2">Rs. {{ number_format($purchase->tax, 2) }}</td>
                            </tr>
                            @endif
                            @if($purchase->shipping_cost > 0)
                            <tr>
                                <td colspan="5" class="text-right py-2 px-2">Shipping:</td>
                                <td class="text-right py-2 px-2">Rs. {{ number_format($purchase->shipping_cost, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="text-lg">
                                <td colspan="5" class="text-right py-2 px-2 font-bold">Grand Total:</td>
                                <td class="text-right py-2 px-2 font-bold text-blue-600">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Payments -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Payment Summary -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-credit-card text-gray-400 mr-2"></i> Payment Summary
                </h4>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Total Amount</span>
                    <span class="font-medium text-gray-900">Rs. {{ number_format($purchase->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Paid Amount</span>
                    <span class="font-medium text-green-600">Rs. {{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3">
                    <span class="text-sm font-medium text-gray-700">Due Amount</span>
                    @if($purchase->status == 'paid')
                    <span class="font-bold text-green-600">Rs. 0.00</span>
                    @else
                    <span class="font-bold {{ $purchase->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rs. {{ number_format($purchase->due_amount, 2) }}
                    </span>
                    @endif
                </div>

                <!-- ✅ Status Badge -->
                <div class="mt-2 p-2 rounded-lg text-center 
            {{ $purchase->status == 'paid' ? 'bg-green-50 border border-green-200' : '' }}
            {{ $purchase->status == 'partial' ? 'bg-yellow-50 border border-yellow-200' : '' }}
            {{ $purchase->status == 'received' ? 'bg-blue-50 border border-blue-200' : '' }}
            {{ $purchase->status == 'draft' ? 'bg-gray-50 border border-gray-200' : '' }}">

                    @if($purchase->status == 'paid')
                    <span class="text-sm text-green-700">
                        <i class="fas fa-check-circle mr-1"></i> Fully Paid
                    </span>
                    @elseif($purchase->status == 'partial')
                    <span class="text-sm text-yellow-700">
                        <i class="fas fa-clock mr-1"></i> Partially Paid
                    </span>
                    @elseif($purchase->status == 'received')
                    <span class="text-sm text-blue-700">
                        <i class="fas fa-box mr-1"></i> Received
                    </span>
                    @elseif($purchase->status == 'ordered')
                    <span class="text-sm text-purple-700">
                        <i class="fas fa-shopping-cart mr-1"></i> Ordered
                    </span>
                    @else
                    <span class="text-sm text-gray-700">
                        <i class="fas fa-file mr-1"></i> Draft
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Payment (if not paid) -->
        @if($purchase->due_amount > 0 && $purchase->status != 'cancelled')
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-plus-circle text-green-600 mr-2"></i> Add Payment
                </h4>
            </div>
            <div class="p-6"
                x-data="{
                    method: 'cash',
                    amount: 0,
                    bankServiceCharge: '',
                    cashBalance: {{ (float) $cashBalance }},
                    bankBalance: {{ (float) $bankBalance }},
                    get available() { return this.method === 'cash' ? this.cashBalance : this.bankBalance },
                    get short() { return (parseFloat(this.amount) || 0) > this.available },
                }">
                <form action="{{ route('admin.purchases.add-payment', $purchase) }}" method="POST"
                    @submit="if (short && !confirm('This account only has Rs. ' + available.toFixed(2) + ' available - recording this payment will take it negative. Submit anyway?')) $event.preventDefault()">
                    @csrf

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="amount" x-model="amount"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                min="0.01" max="{{ $purchase->due_amount }}" required>
                            <p class="text-xs text-gray-500 mt-1">Max: Rs. {{ number_format($purchase->due_amount, 2) }}</p>
                            <p class="text-xs mt-1" :class="short ? 'text-orange-600 font-medium' : 'text-gray-400'">
                                Available in <span x-text="method === 'cash' ? 'Cash' : 'Bank'"></span>: Rs. <span x-text="available.toFixed(2)"></span>
                                <template x-if="short"> - insufficient, will go negative</template>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                            <select name="payment_method" x-model="method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="credit_card">Credit Card</option>
                            </select>
                        </div>

                        <div x-show="method !== 'cash'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Bank Service Charge (Optional)
                                <x-help-tooltip>If the bank deducted a service charge for this transfer/cheque/card payment, enter it here - it'll be auto-recorded as a separate paid Expense (category "Bank Charges") dated the same day, so it shows up in the Expense list without any extra data entry.</x-help-tooltip>
                            </label>
                            <input type="number" step="0.01" name="bank_service_charge" x-model="bankServiceCharge" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reference No (Optional)</label>
                            <input type="text" name="reference_no"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Cheque no / Transaction ID">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea name="notes" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Payment notes..."></textarea>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-check mr-1"></i> Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Payment History -->
        @if($purchase->payments->count() > 0)
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history text-gray-400 mr-2"></i> Payment History
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($purchase->payments as $payment)
                    <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium text-gray-900">Rs. {{ number_format($payment->amount, 2) }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d-m-Y') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                </span>
                                @if($payment->reference_no)
                                <p class="text-xs text-gray-500 mt-1">{{ $payment->reference_no }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
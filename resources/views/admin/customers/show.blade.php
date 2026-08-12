@extends('layouts.admin')

@section('title', 'Customer Details')
@section('page-title', 'Customer: ' . $customer->name)

@section('content')
<div x-data="{
    showPaymentModal: false,
    amount: 0,
    method: 'cash',
    bankServiceCharge: '',
    outstandingBalance: {{ (float) $customer->balance }},
    get overBalance() { return (parseFloat(this.amount) || 0) > this.outstandingBalance },
    get extraAmount() { return (parseFloat(this.amount) || 0) - this.outstandingBalance },

    showEditModal: false,
    editPaymentId: null,
    editAmount: 0,
    editDate: '',
    editMethod: 'cash',
    editReference: '',
    editNotes: '',
    openEditModal(p) {
        this.editPaymentId = p.id;
        this.editAmount = p.amount;
        this.editDate = p.payment_date;
        this.editMethod = p.payment_method;
        this.editReference = p.reference_no || '';
        this.editNotes = p.notes || '';
        this.showEditModal = true;
    },
}">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h3>
                <p class="text-sm text-gray-500">{{ $customer->code }}</p>
                <div class="mt-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $customer->email ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-medium">{{ $customer->phone ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Mobile</span><span class="font-medium">{{ $customer->mobile ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">City</span><span class="font-medium">{{ $customer->city ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">CNIC</span><span class="font-medium">{{ $customer->cnic ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">NTN</span><span class="font-medium">{{ $customer->ntn ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Credit Limit</span><span class="font-medium">{{ $customer->formatted_credit_limit }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Credit Days</span><span class="font-medium">{{ $customer->credit_days ?: '-' }}</span></div>
                </div>
                @if($customer->address)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg"><p class="text-xs text-gray-500">Address</p><p class="text-sm text-gray-900">{{ $customer->address }}</p></div>
                @endif
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.customer-ledger', $customer) }}" class="flex-1 px-4 py-2 bg-blue-600 text-white text-center rounded-lg font-medium hover:bg-blue-700">Ledger</a>
                <a href="{{ route('admin.customers.edit', $customer) }}" class="flex-1 px-4 py-2 bg-yellow-600 text-white text-center rounded-lg font-medium hover:bg-yellow-700">Edit</a>
                <a href="{{ route('admin.customers.index') }}" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg font-medium hover:bg-gray-300">Back</a>
            </div>
            <div class="px-6 pb-6">
                <button type="button" @click="showPaymentModal = true"
                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-money-bill-wave mr-1"></i> Receive Payment
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="font-semibold text-gray-900">Financial Summary</h4></div>
            <div class="p-6 space-y-3">
                <div class="flex justify-between"><span class="text-sm text-gray-500">Opening Balance</span><span class="font-medium">{{ $customer->formatted_opening_balance }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Sales</span><span class="font-medium">Rs. {{ number_format($customer->total_sales, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Total Paid (Invoices)</span><span class="font-medium text-green-600">Rs. {{ number_format($customer->total_paid, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-sm text-gray-500">Direct Payments</span><span class="font-medium text-green-600">Rs. {{ number_format($customer->total_direct_paid, 2) }}</span></div>
                <div class="flex justify-between border-t border-gray-200 pt-3"><span class="text-sm font-semibold">Current Balance</span><span class="font-bold {{ $customer->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $customer->formatted_balance }}</span></div>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h4 class="font-semibold text-gray-900"><i class="fas fa-shopping-cart text-gray-400 mr-2"></i> Recent Sales</h4></div>
            <div class="p-6">
                @if($customer->sales->count() > 0)
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-gray-200"><th class="text-left py-2 px-2">Invoice</th><th class="text-left py-2 px-2">Date</th><th class="text-right py-2 px-2">Amount</th><th class="text-right py-2 px-2">Paid</th><th class="text-right py-2 px-2">Due</th><th class="text-center py-2 px-2">Status</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($customer->sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-2"><a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline">{{ $sale->invoice_no }}</a></td>
                            <td class="py-2 px-2">{{ $sale->sale_date->format('d-m-Y') }}</td>
                            <td class="py-2 px-2 text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-green-600">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="py-2 px-2 text-right text-red-600">Rs. {{ number_format($sale->due_amount, 2) }}</td>
                            <td class="py-2 px-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sale->status_color }}">{{ $sale->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else <p class="text-center text-gray-500 py-4">No sales recorded yet.</p> @endif
            </div>
        </div>

        <!-- Direct Payments (not against any invoice - opening balance / advance) -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="font-semibold text-gray-900">
                    <i class="fas fa-hand-holding-usd text-gray-400 mr-2"></i> Direct Payments
                    <span class="text-xs font-normal text-gray-500">(not against an invoice)</span>
                </h4>
            </div>
            <div class="p-6">
                @if($customer->payments->count() > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2">Date</th>
                            <th class="text-left py-2 px-2">Reference</th>
                            <th class="text-right py-2 px-2">Amount</th>
                            <th class="text-left py-2 px-2">Method</th>
                            <th class="text-center py-2 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($customer->payments as $payment)
                        @php
                            $paymentEditData = [
                                'id' => $payment->id,
                                'amount' => $payment->amount,
                                'payment_date' => $payment->payment_date->format('Y-m-d'),
                                'payment_method' => $payment->payment_method,
                                'reference_no' => $payment->reference_no,
                                'notes' => $payment->notes,
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-2">{{ $payment->payment_date->format('d-m-Y') }}</td>
                            <td class="py-2 px-2">{{ $payment->reference_no ?? '-' }}</td>
                            <td class="py-2 px-2 text-right font-medium text-green-600">Rs. {{ number_format($payment->amount, 2) }}</td>
                            <td class="py-2 px-2">{{ $payment->method_label }}</td>
                            <td class="py-2 px-2 text-center">
                                <button type="button" @click='openEditModal(@json($paymentEditData))'
                                    class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <form action="{{ route('admin.customers.payments.destroy', [$customer, $payment]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this payment and reverse its accounting entries?')"
                                        class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-center text-gray-500 py-4">No direct payments recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Receive Payment Modal -->
<div x-show="showPaymentModal" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showPaymentModal = false"></div>

        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-money-bill-wave text-green-600 mr-2"></i> Receive Payment from {{ $customer->name }}
                </h3>
                <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-4">
                Outstanding balance: <span class="font-semibold {{ $customer->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $customer->formatted_balance }}</span>
                — this payment isn't tied to any specific invoice; it reduces the customer's overall balance (e.g. opening balance or an advance).
            </p>

            <form action="{{ route('admin.customers.payments.store', $customer) }}" method="POST"
                @submit="if (overBalance && !confirm('This is Rs. ' + extraAmount.toFixed(2) + ' more than the outstanding balance - the customer will show as overpaid (Extra). Submit anyway?')) $event.preventDefault()">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="amount" x-model="amount"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            min="0.01" required>
                        <p class="text-xs text-gray-500 mt-1">Outstanding: Rs. {{ number_format($customer->balance, 2) }} - you may receive more than this (it will record as Extra/advance).</p>
                        <p class="text-xs mt-1 text-orange-600 font-medium" x-show="overBalance">
                            This is Rs. <span x-text="extraAmount.toFixed(2)"></span> more than the outstanding balance - will show as Extra (overpaid).
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" x-model="method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference No (Optional)</label>
                        <input type="text" name="reference_no"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Cheque no / Transaction ID">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="e.g. Opening balance settlement"></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-check mr-1"></i> Record Payment
                        </button>
                        <button type="button" @click="showPaymentModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div x-show="showEditModal" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showEditModal = false"></div>

        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Payment
                </h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/customers/' . $customer->id . '/payments') }}/' + editPaymentId" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="amount" x-model="editAmount"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            min="0.01" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" x-model="editDate"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" x-model="editMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference No (Optional)</label>
                        <input type="text" name="reference_no" x-model="editReference"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            placeholder="Cheque no / Transaction ID">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2" x-model="editNotes"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                            <i class="fas fa-save mr-1"></i> Update Payment
                        </button>
                        <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
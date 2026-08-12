@extends('layouts.admin')

@section('title', 'Create Purchase Return')
@section('page-title', 'Create Purchase Return')

@section('content')
<div x-data="purchaseReturnForm()" class="space-y-4 sm:space-y-6">
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                <i class="fas fa-undo-alt text-blue-600 mr-2"></i> New Purchase Return
            </h3>
        </div>

        <div class="p-4 sm:p-6">
            <form action="{{ route('admin.purchase-returns.store') }}" method="POST">
                @csrf

                <!-- Purchase Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Select Purchase <span class="text-red-500">*</span>
                            <x-help-tooltip>Only purchases that actually posted stock/payable (Received, Partial, or Paid) are listed - stock this return can't reduce below zero has to have been received first. Submitting this form immediately removes the returned quantities from stock and posts the ledger entries below; there's no edit afterward, only view or delete (deleting reverses it again).</x-help-tooltip>
                        </label>
                        <select name="purchase_id" x-model="purchaseId" @change="loadPurchaseDetails()" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Purchase</option>
                            @foreach($purchases as $purchase)
                            <option value="{{ $purchase->id }}">
                                {{ $purchase->invoice_no }} - {{ $purchase->supplier->name }} ({{ $purchase->purchase_date->format('d-m-Y') }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Return Date <span class="text-red-500">*</span></label>
                        <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Refund Method <span class="text-red-500">*</span>
                            <x-help-tooltip>How this specific return is being settled - independent of how the original purchase was paid. Cash/Bank Transfer/Cheque move money into that account (the supplier paying you back). Credit moves no cash at all; it just reduces what you owe the supplier.</x-help-tooltip>
                        </label>
                        <select name="refund_method" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <!-- Purchase Details -->
                <div x-show="purchaseId" class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Purchase Details</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="text-gray-500">Invoice:</span> <span class="font-medium" x-text="purchaseDetails.invoice_no"></span></div>
                        <div><span class="text-gray-500">Supplier:</span> <span class="font-medium" x-text="purchaseDetails.supplier_name"></span></div>
                        <div><span class="text-gray-500">Date:</span> <span class="font-medium" x-text="purchaseDetails.purchase_date"></span></div>
                        <div><span class="text-gray-500">Total:</span> <span class="font-medium text-blue-600" x-text="'Rs. ' + purchaseDetails.total_amount"></span></div>
                    </div>
                </div>

                <!-- Return Items -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-medium text-gray-700">
                            <i class="fas fa-list text-gray-400 mr-1"></i> Return Items <span class="text-red-500">*</span>
                            <span class="ml-1 text-xs text-gray-500" x-text="'(' + items.length + ' items)'"></span>
                        </label>
                        <button type="button" @click="addRow()" x-show="purchaseId"
                                class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-plus mr-1"></i> Add Product
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px]">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 min-w-[150px]">Product</th>
                                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[80px]">Qty</th>
                                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[100px]">Price</th>
                                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[80px]">Disc</th>
                                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[80px]">Tax</th>
                                    <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[100px]">Total</th>
                                    <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[50px]"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition-colors duration-150">
                                        <td class="py-1.5 px-1.5">
                                            <select :name="'items['+index+'][product_id]'" 
                                                    x-model="item.product_id" 
                                                    @change="calculateRow(index)"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="">Select Product</option>
                                                <template x-for="product in purchaseDetails.items" :key="product.id">
                                                    <option :value="product.id" 
                                                            :data-purchase-item-id="product.purchase_item_id"
                                                            :data-price="product.unit_price"
                                                            :data-discount="product.discount"
                                                            :data-tax="product.tax"
                                                            x-text="product.product_name + ' (Max: ' + product.quantity + ')'">
                                                    </option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-1.5 px-1.5">
                                            <input type="number" step="0.01" 
                                                   :name="'items['+index+'][quantity]'" 
                                                   x-model="item.quantity"
                                                   @input="calculateRow(index)"
                                                   class="w-full px-1 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   min="0.01" step="0.01">
                                        </td>
                                        <td class="py-1.5 px-1.5">
                                            <input type="number" step="0.01" 
                                                   :name="'items['+index+'][unit_price]'" 
                                                   x-model="item.unit_price"
                                                   @input="calculateRow(index)"
                                                   class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   min="0" step="0.01">
                                        </td>
                                        <td class="py-1.5 px-1.5">
                                            <input type="number" step="0.01" 
                                                   :name="'items['+index+'][discount]'" 
                                                   x-model="item.discount"
                                                   @input="calculateRow(index)"
                                                   class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   min="0" step="0.01">
                                        </td>
                                        <td class="py-1.5 px-1.5">
                                            <input type="number" step="0.01" 
                                                   :name="'items['+index+'][tax]'" 
                                                   x-model="item.tax"
                                                   @input="calculateRow(index)"
                                                   class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   min="0" step="0.01">
                                        </td>
                                        <td class="py-1.5 px-1.5 text-right">
                                            <span class="text-sm font-medium text-blue-600" x-text="'Rs. ' + item.total.toFixed(2)"></span>
                                        </td>
                                        <td class="py-1.5 px-1.5 text-center">
                                            <button type="button" @click="removeRow(index)" 
                                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded-lg transition-colors duration-200">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="items.length === 0" class="text-center py-8 text-gray-400">
                        <i class="fas fa-box-open text-3xl mb-2 block"></i>
                        <p class="text-sm">No products added. Select a purchase and add products to return.</p>
                    </div>
                </div>

                <!-- Reason & Notes -->
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Return</label>
                            <textarea name="reason" rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex justify-end">
                        <div class="w-64 space-y-1 text-right">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Sub Total:</span>
                                <span class="text-sm font-semibold" x-text="'Rs. ' + subTotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Discount:</span>
                                <span class="text-sm text-red-600" x-text="'- Rs. ' + discountAmount.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Tax:</span>
                                <span class="text-sm" x-text="'Rs. ' + tax.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2">
                                <span class="text-base font-bold text-gray-900">Total Return:</span>
                                <span class="text-lg font-bold text-red-600" x-text="'Rs. ' + grandTotal.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sub_total" x-bind:value="subTotal">

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i> Create Return
                    </button>
                    <a href="{{ route('admin.purchase-returns.index') }}" class="w-full sm:w-auto px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200 text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function purchaseReturnForm() {
    return {
        purchaseId: '',
        purchaseDetails: { items: [], supplier_name: '', invoice_no: '', purchase_date: '', total_amount: 0 },
        items: [],
        subTotal: 0,
        discountAmount: 0,
        tax: 0,
        grandTotal: 0,

        loadPurchaseDetails() {
            if (!this.purchaseId) {
                this.purchaseDetails = { items: [], supplier_name: '', invoice_no: '', purchase_date: '', total_amount: 0 };
                this.items = [];
                return;
            }

            fetch(`/admin/purchase-returns/get-purchase-details/${this.purchaseId}`)
                .then(response => response.json())
                .then(data => {
                    this.purchaseDetails = {
                        items: data.items.map(item => ({
                            id: item.id,
                            product_id: item.product_id,
                            product_name: item.product.name,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            discount: item.discount,
                            tax: item.tax,
                            total_price: item.total_price,
                            purchase_item_id: item.id
                        })),
                        supplier_name: data.supplier.name,
                        invoice_no: data.invoice_no,
                        purchase_date: data.purchase_date,
                        total_amount: data.total_amount
                    };
                    this.items = [];
                    this.addRow();
                    this.calculateTotals();
                });
        },

        addRow() {
            this.items.push({
                purchase_item_id: '',
                product_id: '',
                quantity: 1,
                unit_price: 0,
                discount: 0,
                tax: 0,
                total: 0
            });
            this.calculateTotals();
        },

        removeRow(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },

        calculateRow(index) {
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            const disc = parseFloat(item.discount) || 0;
            const tax = parseFloat(item.tax) || 0;
            item.total = (qty * price) - disc + tax;
            this.calculateTotals();
        },

        calculateTotals() {
            this.subTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
            this.discountAmount = this.items.reduce((sum, item) => sum + (parseFloat(item.discount) || 0), 0);
            this.tax = this.items.reduce((sum, item) => sum + (parseFloat(item.tax) || 0), 0);
            this.grandTotal = this.subTotal - this.discountAmount + this.tax;
        }
    }
}
</script>
@endsection
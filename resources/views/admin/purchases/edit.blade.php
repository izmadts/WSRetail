@extends('layouts.admin')

@section('title', 'Edit Purchase')
@section('page-title', 'Edit Purchase: ' . $purchase->invoice_no)

@section('content')
<div x-data="purchaseForm()" class="space-y-4 sm:space-y-6">
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Purchase
            </h3>
        </div>
        
        <div class="p-4 sm:p-6">
            <form action="{{ route('admin.purchases.update', $purchase) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Supplier -->
                    <div class="sm:col-span-2">
                        <label for="supplier_id" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('supplier_id') border-red-500 @enderror"
                            id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('supplier_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    
                    <!-- Purchase Date -->
                    <div>
                        <label for="purchase_date" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('purchase_date') border-red-500 @enderror"
                            id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                        @error('purchase_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    
                    <!-- Payment Term -->
                    <div>
                        <label for="payment_term" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Payment <span class="text-red-500">*</span>
                            <x-help-tooltip>Decides which ledger account is credited once this purchase posts - Cash directly, or Accounts Payable for Credit. It doesn't force full payment by itself; whether it's fully settled depends on the Status you choose below.</x-help-tooltip>
                        </label>
                        <select class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_term') border-red-500 @enderror"
                            id="payment_term" name="payment_term" required>
                            <option value="cash" {{ old('payment_term', $purchase->payment_term) == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit" {{ old('payment_term', $purchase->payment_term) == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                        @error('payment_term')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                
                <!-- Status & Notes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mt-3 sm:mt-4">
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                            <x-help-tooltip>Draft and Ordered don't touch stock or the ledger yet. Received posts the stock in and records the payable as unpaid (add a payment afterwards to settle it). Paid does the same but also records the full amount as settled immediately.</x-help-tooltip>
                        </label>
                        <select class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror"
                            id="status" name="status" required>
                            <option value="draft" {{ old('status', $purchase->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="ordered" {{ old('status', $purchase->status) == 'ordered' ? 'selected' : '' }}>Ordered</option>
                            <option value="received" {{ old('status', $purchase->status) == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="paid" {{ old('status', $purchase->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                        @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" name="notes" placeholder="Add notes..." value="{{ old('notes', $purchase->notes) }}"
                            class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <!-- Product Items -->
                <div class="mt-4 sm:mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <label class="text-sm font-medium text-gray-700">
                            <i class="fas fa-boxes text-gray-400 mr-1"></i> Products <span class="text-red-500">*</span>
                            <span class="ml-1 text-xs text-gray-500" x-text="'(' + items.length + ' items)'"></span>
                        </label>
                        <button type="button" @click="addRow()"
                            class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-plus mr-1"></i> Add Product
                        </button>
                    </div>
                    
                    <!-- Desktop Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full min-w-[700px]">
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
                                                @change="onProductChange(index, $event)"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                                                    {{ Str::limit($product->name, 25) }} ({{ $product->code }})
                                                </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-orange-600" x-show="overCost(item)">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Well above last cost (Rs. <span x-text="item.expectedCost"></span>)
                                            </p>
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
                                                :class="overCost(item) ? 'border-orange-400 bg-orange-50' : ''"
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
                            <tfoot>
                                <tr class="bg-gray-50 font-medium">
                                    <td colspan="5" class="text-right py-2 px-2 text-sm">Sub Total:</td>
                                    <td class="text-right py-2 px-2 text-sm" x-text="'Rs. ' + subTotal.toFixed(2)"></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right py-1.5 px-2 text-xs text-gray-600">Discount:</td>
                                    <td class="py-1.5 px-1.5">
                                        <div class="flex gap-1">
                                            <input type="number" step="0.01" name="discount" x-model="discount" @input="calculateTotals()"
                                                class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                min="0" step="0.01">
                                            <select name="discount_type" class="w-24 px-1 py-1 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="fixed" {{ $purchase->discount_type == 'fixed' ? 'selected' : '' }}>Rs</option>
                                                <option value="percentage" {{ $purchase->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="text-right py-1.5 px-2 text-sm" x-text="'Rs. ' + (discountAmount > 0 ? discountAmount.toFixed(2) : '0.00')"></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right py-1.5 px-2 text-xs text-gray-600">Tax:</td>
                                    <td class="py-1.5 px-1.5">
                                        <input type="number" step="0.01" name="tax" x-model="tax" @input="calculateTotals()"
                                            class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            min="0" step="0.01">
                                    </td>
                                    <td class="text-right py-1.5 px-2 text-sm" x-text="'Rs. ' + tax.toFixed(2)"></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-right py-1.5 px-2 text-xs text-gray-600">Shipping:</td>
                                    <td class="py-1.5 px-1.5">
                                        <input type="number" step="0.01" name="shipping_cost" x-model="shipping" @input="calculateTotals()"
                                            class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            min="0" step="0.01">
                                    </td>
                                    <td class="text-right py-1.5 px-2 text-sm" x-text="'Rs. ' + shipping.toFixed(2)"></td>
                                    <td></td>
                                </tr>
                                <tr class="bg-blue-50 font-bold">
                                    <td colspan="5" class="text-right py-2.5 px-2 text-base sm:text-lg">Grand Total:</td>
                                    <td class="text-right py-2.5 px-2 text-base sm:text-lg text-blue-600" x-text="'Rs. ' + grandTotal.toFixed(2)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                <div class="space-y-2">
                                    <div>
                                        <select :name="'items['+index+'][product_id]'"
                                            x-model="item.product_id"
                                            @change="onProductChange(index, $event)"
                                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                                                {{ $product->name }} ({{ $product->code }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-orange-600" x-show="overCost(item)">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Well above last cost (Rs. <span x-text="item.expectedCost"></span>)
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="text-xs text-gray-500">Qty</label>
                                            <input type="number" step="0.01"
                                                :name="'items['+index+'][quantity]'"
                                                x-model="item.quantity"
                                                @input="calculateRow(index)"
                                                class="w-full px-2 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                min="0.01" step="0.01">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Price</label>
                                            <input type="number" step="0.01"
                                                :name="'items['+index+'][unit_price]'"
                                                x-model="item.unit_price"
                                                @input="calculateRow(index)"
                                                class="w-full px-2 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                :class="overCost(item) ? 'border-orange-400 bg-orange-50' : ''"
                                                min="0" step="0.01">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Total</label>
                                            <p class="text-sm font-semibold text-blue-600 text-right pt-1" x-text="'Rs. ' + item.total.toFixed(2)"></p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs text-gray-500">Discount</label>
                                            <input type="number" step="0.01"
                                                :name="'items['+index+'][discount]'"
                                                x-model="item.discount"
                                                @input="calculateRow(index)"
                                                class="w-full px-2 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                min="0" step="0.01">
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Tax</label>
                                            <input type="number" step="0.01"
                                                :name="'items['+index+'][tax]'"
                                                x-model="item.tax"
                                                @input="calculateRow(index)"
                                                class="w-full px-2 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                min="0" step="0.01">
                                        </div>
                                    </div>
                                    
                                    <button type="button" @click="removeRow(index)"
                                        class="w-full mt-1 text-center text-red-500 hover:text-red-700 text-sm py-1 border border-red-200 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                        <i class="fas fa-trash mr-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="items.length === 0" class="text-center py-8 text-gray-400">
                        <i class="fas fa-box-open text-3xl mb-2 block"></i>
                        <p class="text-sm">No products added. Click "Add Product" to start.</p>
                    </div>
                </div>
                
                <!-- Hidden field for sub_total -->
                <input type="hidden" name="sub_total" x-bind:value="subTotal">
                
                <!-- Submit Buttons -->
                <div class="mt-4 sm:mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i> Update Purchase
                    </button>
                    <a href="{{ route('admin.purchases.index') }}" class="w-full sm:w-auto px-4 sm:px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200 text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function purchaseForm() {
    // Initialize with existing items from PHP
    @php
        $itemsJson = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) $item->discount,
                'tax' => (float) $item->tax,
                'total' => (float) $item->total_price
            ];
        })->toJson();
    @endphp
    
    const existingItems = {!! $itemsJson !!};

    return {
        items: existingItems.length > 0 ? existingItems : [],
        discount: parseFloat({{ $purchase->discount ?? 0 }}),
        tax: parseFloat({{ $purchase->tax ?? 0 }}),
        shipping: parseFloat({{ $purchase->shipping_cost ?? 0 }}),
        subTotal: 0,
        discountAmount: 0,
        grandTotal: 0,

        init() {
            if (this.items.length === 0) {
                this.addRow();
            }
            this.calculateTotals();
        },

        addRow() {
            this.items.push({
                product_id: '',
                quantity: 1,
                unit_price: 0,
                discount: 0,
                tax: 0,
                total: 0,
                expectedCost: null
            });
            this.calculateTotals();
        },

        removeRow(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },

        onProductChange(index, event) {
            const option = event.target.selectedOptions[0];
            const item = this.items[index];
            if (option && option.value) {
                const price = parseFloat(option.dataset.price) || 0;
                item.unit_price = price;
                item.expectedCost = price;
            } else {
                item.expectedCost = null;
            }
            this.calculateRow(index);
        },

        // Flags a unit price more than 50% above the product's last
        // recorded purchase price - catches fat-finger entry without
        // blocking genuinely higher supplier quotes.
        overCost(item) {
            if (!item.product_id || !item.expectedCost || item.expectedCost <= 0) return false;
            return (parseFloat(item.unit_price) || 0) > item.expectedCost * 1.5;
        },

        calculateRow(index) {
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            const disc = parseFloat(item.discount) || 0;
            const tax = parseFloat(item.tax) || 0;

            const subtotal = qty * price;
            item.total = subtotal - disc + tax;
            this.calculateTotals();
        },

        calculateTotals() {
            // Calculate sub total
            this.subTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);

            // Calculate grand total
            const discount = parseFloat(this.discount) || 0;
            const tax = parseFloat(this.tax) || 0;
            const shipping = parseFloat(this.shipping) || 0;

            // Apply discount (if percentage, calculate from sub total)
            const discountType = document.querySelector('[name="discount_type"]');
            let discountAmount = 0;
            
            if (discountType) {
                discountAmount = discountType.value === 'percentage' 
                    ? (this.subTotal * discount / 100) 
                    : discount;
            } else {
                discountAmount = discount;
            }

            this.discountAmount = discountAmount;
            this.grandTotal = this.subTotal - discountAmount + tax + shipping;
        }
    }
}
</script>
@endsection
@extends('layouts.agent')
@section('title', 'New Sale')
@section('page-title', 'Create Sale')
@section('content')
<div x-data="saleForm()" class="space-y-4 sm:space-y-6">
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-plus-circle text-green-600 mr-2"></i> New Sale</h3></div>
        <div class="p-6">
            <form action="{{ route('agent.sales.store') }}" method="POST" @submit="onSubmit($event)">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" x-model="customer_id" @change="onCustomerChange($event)" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-price-field="{{ $customer->customerGroup->price_field ?? 'sale_price' }}">
                                {{ $customer->name }}@if($customer->customerGroup) - {{ $customer->customerGroup->name }}@endif
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500" x-show="customer_id" x-text="priceField === 'wholesale_price' ? 'Wholesale pricing applies' : 'Retail pricing applies'"></p>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label><input type="date" name="sale_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment <span class="text-red-500">*</span></label><select name="payment_term" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"><option value="cash">Cash</option><option value="credit">Credit</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label><select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"><option value="draft">Draft</option><option value="confirmed">Confirmed</option></select>
                        <p class="mt-1 text-xs text-gray-500">'Paid'/'Partial' aren't picked here - enter an amount received below instead.</p>
                    </div>
                </div>

                <div class="mt-6"><div class="flex items-center justify-between"><label class="text-sm font-medium text-gray-700">Products <span class="text-red-500">*</span> <span class="text-xs text-gray-500" x-text="'('+items.length+' items)'"></span></label><button type="button" @click="addRow()" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700"><i class="fas fa-plus"></i> Add</button></div>
                    <div class="overflow-x-auto mt-2"><table class="w-full min-w-[600px]"><thead><tr class="bg-gray-50 border-b"><th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Product</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-20">Qty</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-24">Price</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-20">Disc</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-20">Tax</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-24">Total</th><th class="text-center w-10"></th></tr></thead>
                        <tbody>
                            <template x-for="(item,index) in items" :key="index">
                                <tr>
                                    <td class="py-1 px-1">
                                        <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="onProductChange(index)" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg">
                                            <option value="">Select</option>
                                            <template x-for="p in visibleProducts(item.product_id)" :key="p.id">
                                                <option :value="p.id" x-text="p.name + ' - Stock: ' + p.current_stock"></option>
                                            </template>
                                        </select>
                                        <p class="mt-1 text-xs" x-show="item.product_id">
                                            <span x-show="stockWarning(item)" class="text-orange-600">Only <span x-text="item.stock"></span> in stock</span>
                                            <span x-show="belowCost(item)" class="text-red-600 block">Below cost (Rs. <span x-text="item.cost"></span>)</span>
                                        </p>
                                    </td>
                                    <td class="py-1 px-1"><input type="number" step="0.01" :name="'items['+index+'][quantity]'" x-model="item.quantity" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-center border border-gray-300 rounded-lg" :class="stockWarning(item) ? 'border-orange-400 bg-orange-50' : ''" min="0.01"></td>
                                    <td class="py-1 px-1"><input type="number" step="0.01" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg" :class="belowCost(item) ? 'border-red-400 bg-red-50' : ''" min="0"></td>
                                    <td class="py-1 px-1"><input type="number" step="0.01" :name="'items['+index+'][discount]'" x-model="item.discount" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg" min="0"></td>
                                    <td class="py-1 px-1"><input type="number" step="0.01" :name="'items['+index+'][tax]'" x-model="item.tax" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg" min="0"></td>
                                    <td class="py-1 px-1 text-right"><span class="text-sm font-medium text-green-600" x-text="'Rs. '+item.total.toFixed(2)"></span></td>
                                    <td class="py-1 px-1 text-center"><button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table></div>
                    <div x-show="items.length===0" class="text-center py-6 text-gray-400"><i class="fas fa-box-open text-2xl"></i><p>No products</p></div>
                </div>

                <div x-show="hasBelowCost()" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <label class="flex items-start gap-2 text-sm text-red-800">
                        <input type="checkbox" x-model="confirmBelowCost" class="mt-0.5 h-4 w-4 text-red-600 rounded">
                        <span>One or more items are priced below their purchase cost. I confirm this is intentional and want to proceed anyway.</span>
                    </label>
                </div>

                <div class="mt-4 border-t pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700">Notes</label><input type="text" name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div class="space-y-1 text-right">
                        <div><span class="text-sm text-gray-600">Sub Total:</span> <span class="text-sm font-semibold" x-text="'Rs. '+subTotal.toFixed(2)"></span></div>
                        <div><span class="text-sm text-gray-600">Discount:</span> <input type="number" step="0.01" name="discount" x-model="discount" @input="calculateTotals()" class="w-20 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg inline-block" min="0"><select name="discount_type" x-model="discountType" @change="calculateTotals()" class="w-16 px-1 py-1 text-xs border border-gray-300 rounded-lg inline-block"><option value="fixed">Rs</option><option value="percentage">%</option></select> <span class="text-sm text-red-600" x-text="'- Rs. '+discountAmount.toFixed(2)"></span></div>
                        <div><span class="text-sm text-gray-600">Tax:</span> <input type="number" step="0.01" name="tax" x-model="tax" @input="calculateTotals()" class="w-20 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg inline-block" min="0"> <span class="text-sm" x-text="'Rs. '+tax.toFixed(2)"></span></div>
                        <div><span class="text-sm text-gray-600">Shipping:</span> <input type="number" step="0.01" name="shipping_cost" x-model="shipping" @input="calculateTotals()" class="w-20 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg inline-block" min="0"> <span class="text-sm" x-text="'Rs. '+shipping.toFixed(2)"></span></div>
                        <div class="bg-green-50 p-2 rounded-lg border-2 border-green-200"><span class="font-bold">Grand Total:</span> <span class="text-lg font-bold text-green-600" x-text="'Rs. '+grandTotal.toFixed(2)"></span></div>
                        <div class="pt-1"><span class="text-sm text-gray-600">Amount Received Now:</span> <input type="number" step="0.01" name="amount_received" x-model="amountReceived" min="0" :max="grandTotal" class="w-24 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg inline-block" placeholder="0.00"> <button type="button" @click="amountReceived = grandTotal.toFixed(2)" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Pay in Full</button></div>
                        <p class="text-xs text-gray-500">Leave blank for no payment yet.</p>
                    </div>
                </div>
                <input type="hidden" name="sub_total" x-bind:value="subTotal">
                <div class="mt-4 flex gap-3"><button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">Create Sale</button><a href="{{ route('agent.sales.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">Cancel</a></div>
            </form>
        </div>
    </div>
</div>
<script>
function saleForm() {
    var allProducts = @json($productsForJs);

    return {
        items: [],
        customer_id: '',
        priceField: 'sale_price',
        discount: 0,
        discountType: 'fixed',
        tax: 0,
        shipping: 0,
        amountReceived: '',
        subTotal: 0,
        discountAmount: 0,
        grandTotal: 0,
        confirmBelowCost: false,
        allProducts: allProducts,

        init() { this.addRow(); },

        onCustomerChange(event) {
            var option = event.target.selectedOptions[0];
            this.priceField = (option && option.dataset.priceField) ? option.dataset.priceField : 'sale_price';

            this.items.forEach((item, index) => {
                if (!item.product_id) return;
                var product = this.allProducts.find(p => p.id == item.product_id);
                var stillAllowed = product && (this.priceField === 'wholesale_price' ? product.is_wholesale : product.is_retail);
                if (!stillAllowed) {
                    item.product_id = '';
                    item.unit_price = 0;
                    item.stock = null;
                    item.cost = null;
                } else {
                    item.unit_price = this.priceField === 'wholesale_price' ? product.wholesale_price : product.sale_price;
                }
                this.calculateRow(index);
            });
        },

        visibleProducts(currentProductId = null) {
            return this.allProducts.filter(p =>
                (this.priceField === 'wholesale_price' ? p.is_wholesale : p.is_retail) || p.id == currentProductId
            );
        },

        addRow() {
            this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount: 0, tax: 0, total: 0, stock: null, cost: null });
            this.calculateTotals();
        },

        removeRow(index) {
            if (this.items.length > 1) { this.items.splice(index, 1); this.calculateTotals(); }
        },

        onProductChange(index) {
            var item = this.items[index];
            var product = this.allProducts.find(p => p.id == item.product_id);
            if (product) {
                item.unit_price = this.priceField === 'wholesale_price' ? product.wholesale_price : product.sale_price;
                item.cost = product.purchase_price;
                item.stock = product.current_stock;
            } else {
                item.stock = null;
                item.cost = null;
            }
            this.calculateRow(index);
        },

        requestedQtyFor(productId) {
            return this.items
                .filter(i => i.product_id == productId)
                .reduce((sum, i) => sum + (parseFloat(i.quantity) || 0), 0);
        },

        stockWarning(item) {
            if (!item.product_id || item.stock === null) return false;
            return this.requestedQtyFor(item.product_id) > item.stock;
        },

        belowCost(item) {
            if (!item.product_id || item.cost === null || item.cost <= 0) return false;
            return (parseFloat(item.unit_price) || 0) < item.cost;
        },

        hasBelowCost() {
            return this.items.some(i => this.belowCost(i));
        },

        hasStockWarning() {
            return this.items.some(i => this.stockWarning(i));
        },

        calculateRow(index) {
            var item = this.items[index];
            var qty = parseFloat(item.quantity) || 0;
            var price = parseFloat(item.unit_price) || 0;
            var disc = parseFloat(item.discount) || 0;
            var tax = parseFloat(item.tax) || 0;
            item.total = (qty * price) - disc + tax;
            this.calculateTotals();
        },

        calculateTotals() {
            this.subTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
            var discount = parseFloat(this.discount) || 0;
            this.discountAmount = this.discountType === 'percentage' ? (this.subTotal * discount / 100) : discount;
            var tax = parseFloat(this.tax) || 0;
            var shipping = parseFloat(this.shipping) || 0;
            this.grandTotal = this.subTotal - this.discountAmount + tax + shipping;
        },

        onSubmit(event) {
            if (this.hasBelowCost() && !this.confirmBelowCost) {
                event.preventDefault();
                alert('One or more items are priced below cost. Please confirm the below-cost checkbox before submitting.');
                return;
            }
            if (this.hasStockWarning() && !confirm('One or more items exceed available stock. Submit anyway?')) {
                event.preventDefault();
            }
        }
    }
}
</script>
@endsection

@extends('layouts.admin')

@section('title', 'New Sale')
@section('page-title', 'Create Sale')

@section('content')
<div x-data="saleForm()" class="space-y-4 sm:space-y-6">
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900"><i class="fas fa-plus-circle text-blue-600 mr-2"></i> New Sale</h3>
        </div>

        <div class="p-4 sm:p-6">
            <form action="{{ route('admin.sales.store') }}" method="POST" @submit="onSubmit($event)">
                @csrf

                <!-- Header Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="sm:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" x-model="customer_id" @change="onCustomerChange($event)" required class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-price-field="{{ $customer->customerGroup->price_field ?? 'sale_price' }}">
                                {{ $customer->name }} ({{ $customer->code }})@if($customer->customerGroup) - {{ $customer->customerGroup->name }}@endif
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500" x-show="customer_id" x-text="priceField === 'wholesale_price' ? 'Wholesale pricing applies' : 'Retail pricing applies'"></p>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Agent
                            <x-help-tooltip>Selecting an agent credits them with commission on this sale, calculated per their commission tier (shown as "Est. Commission" below) - it doesn't change what the customer pays. Leave blank for a walk-in / no-agent sale.</x-help-tooltip>
                        </label>
                        <select name="agent_id" x-model="agent_id" @change="calculateTotals()" class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Agent</option>
                            @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" required class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Payment <span class="text-red-500">*</span>
                            <x-help-tooltip>Cash requires full payment now - a Cash sale can't be confirmed with only part paid; save as Draft instead, or switch to Credit if the customer will pay over time. Credit allows partial or deferred payment and posts any unpaid balance to the customer's account.</x-help-tooltip>
                        </label>
                        <select name="payment_term" x-model="payment_term" @change="calculateTotals()" required class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                            <x-help-tooltip>Draft doesn't touch stock or the ledger yet - use it for a quote or work-in-progress order you're not ready to commit. Confirmed posts it for real. "Paid" and "Partial" aren't chosen here - they're set automatically from whatever you enter in Amount Received Now below.</x-help-tooltip>
                        </label>
                        <select name="status" required class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="draft">Draft</option>
                            <option value="confirmed">Confirmed</option>
                        </select>
                    </div>
                </div>

                <!-- Products -->
                <div class="mt-4 sm:mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <label class="text-sm font-medium text-gray-700"><i class="fas fa-boxes text-gray-400 mr-1"></i> Products <span class="text-red-500">*</span> <span class="ml-1 text-xs text-gray-500" x-text="'(' + items.length + ' items)'"></span></label>
                        <button type="button" @click="addRow()" class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors duration-200"><i class="fas fa-plus mr-1"></i> Add Product</button>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full min-w-[700px]">
                            <thead><tr class="bg-gray-50 border-b border-gray-200"><th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 min-w-[150px]">Product</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[80px]">Qty</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[100px]">Price</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[80px]">Disc</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[80px]">Tax</th><th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[100px]">Total</th><th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-[50px]"></th></tr></thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition-colors duration-150">
                                        <td class="py-1.5 px-1.5">
                                            <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="onProductChange(index)" class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="">Select Product</option>
                                                <template x-for="p in visibleProducts(item.product_id)" :key="p.id">
                                                    <option :value="p.id" x-text="p.name + ' (' + p.code + ') - Stock: ' + p.current_stock"></option>
                                                </template>
                                            </select>
                                            <p class="mt-1 text-xs" x-show="item.product_id">
                                                <span x-show="stockWarning(item)" class="text-orange-600"><i class="fas fa-exclamation-triangle mr-1"></i>Only <span x-text="item.stock"></span> in stock (requesting <span x-text="requestedQtyFor(item.product_id)"></span>)</span>
                                                <span x-show="belowCost(item)" class="text-red-600 block"><i class="fas fa-exclamation-circle mr-1"></i>Below cost (Rs. <span x-text="item.cost"></span>)</span>
                                            </p>
                                        </td>
                                        <td class="py-1.5 px-1.5"><type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" :class="stockWarning(item) ? 'border-orange-400 bg-orange-50' : ''" min="0.01" step="0.01"></td>
                                        <td class="py-1.5 px-1.5"><type="number" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" :class="belowCost(item) ? 'border-red-400 bg-red-50' : ''" min="0" step="0.01"></td>
                                        <td class="py-1.5 px-1.5"><type="number" :name="'items['+index+'][discount]'" x-model="item.discount" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" step="0.01"></td>
                                        <td class="py-1.5 px-1.5"><type="number" :name="'items['+index+'][tax]'" x-model="item.tax" @input="calculateRow(index)" class="w-full px-1 py-1 text-sm text-right border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" step="0.01"></td>
                                        <td class="py-1.5 px-1.5 text-right"><span class="text-sm font-medium text-blue-600" x-text="'Rs. ' + item.total.toFixed(2)"></span></td>
                                        <td class="py-1.5 px-1.5 text-center"><button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded-lg"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div class="sm:hidden space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 space-y-2">
                                <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="onProductChange(index)" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg bg-white">
                                    <option value="">Select Product</option>
                                    <template x-for="p in visibleProducts(item.product_id)" :key="p.id">
                                        <option :value="p.id" x-text="p.name + ' - Stock: ' + p.current_stock"></option>
                                    </template>
                                </select>
                                <p class="text-xs" x-show="item.product_id">
                                    <span x-show="stockWarning(item)" class="text-orange-600"><i class="fas fa-exclamation-triangle mr-1"></i>Only <span x-text="item.stock"></span> in stock</span>
                                    <span x-show="belowCost(item)" class="text-red-600 block"><i class="fas fa-exclamation-circle mr-1"></i>Below cost (Rs. <span x-text="item.cost"></span>)</span>
                                </p>
                                <div class="grid grid-cols-3 gap-2"><div><label class="text-xs text-gray-500">Qty</label><type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" @input="calculateRow(index)" class="w-full px-2 py-1 text-sm text-center border border-gray-300 rounded-lg" min="0.01" step="0.01"></div><div><label class="text-xs text-gray-500">Price</label><type="number" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" @input="calculateRow(index)" class="w-full px-2 py-1 text-sm text-right border border-gray-300 rounded-lg" min="0" step="0.01"></div><div><label class="text-xs text-gray-500">Total</label><p class="text-sm font-semibold text-blue-600 text-right pt-1" x-text="'Rs. ' + item.total.toFixed(2)"></p></div></div>
                                <button type="button" @click="removeRow(index)" class="w-full text-center text-red-500 hover:text-red-700 text-sm py-1 border border-red-200 rounded-lg hover:bg-red-50">Remove</button>
                            </div>
                        </template>
                    </div>

                    <div x-show="items.length === 0" class="text-center py-8 text-gray-400"><i class="fas fa-box-open text-3xl mb-2 block"></i><p class="text-sm">No products added. Click "Add Product" to start.</p></div>
                </div>

                <!-- Below-cost confirmation gate -->
                <div x-show="hasBelowCost()" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <label class="flex items-start gap-2 text-sm text-red-800">
                        <input type="checkbox" x-model="confirmBelowCost" class="mt-0.5 h-4 w-4 text-red-600 rounded">
                        <span>One or more items are priced below their purchase cost. I confirm this is intentional and want to proceed anyway.</span>
                    </label>
                </div>

                <!-- Totals Footer -->
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" name="notes" placeholder="Add notes..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 justify-end">
                                <span class="text-sm text-gray-600">Sub Total:</span>
                                <span class="text-sm font-semibold" x-text="'Rs. ' + subTotal.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center gap-2 justify-end">
                                <span class="text-sm text-gray-600">Discount:</span>
                                <type="number" name="discount" x-model="discount" @input="calculateTotals()" class="w-24 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg" min="0" step="0.01">
                                <select name="discount_type" x-model="discountType" @change="calculateTotals()" class="w-16 px-1 py-1 text-xs border border-gray-300 rounded-lg"><option value="fixed">Rs</option><option value="percentage">%</option></select>
                                <span class="text-sm text-red-600" x-text="'- Rs. ' + discountAmount.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center gap-2 justify-end">
                                <span class="text-sm text-gray-600">Tax:</span>
                                <type="number" name="tax" x-model="tax" @input="calculateTotals()" class="w-24 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg" min="0" step="0.01">
                                <span class="text-sm" x-text="'Rs. ' + tax.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center gap-2 justify-end">
                                <span class="text-sm text-gray-600">Shipping:</span>
                                <type="number" name="shipping_cost" x-model="shipping" @input="calculateTotals()" class="w-24 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg" min="0" step="0.01">
                                <span class="text-sm" x-text="'Rs. ' + shipping.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center gap-2 justify-end border-t border-gray-200 pt-2" x-show="agent_id">
                                <span class="text-sm font-semibold text-gray-700">Est. Commission:</span>
                                <span class="text-sm font-semibold text-purple-600" x-text="'Rs. ' + commissionAmount.toFixed(2)"></span>
                            </div>
                            <p class="text-xs text-gray-500 text-right" x-show="agent_id" x-text="commissionNote"></p>
                            <div class="flex items-center gap-2 justify-end bg-blue-50 p-2 rounded-lg border-2 border-blue-200">
                                <span class="text-base font-bold text-gray-700">Grand Total:</span>
                                <span class="text-lg font-bold text-blue-600" x-text="'Rs. ' + grandTotal.toFixed(2)"></span>
                            </div>
                            <div class="flex items-center gap-2 justify-end pt-1">
                                <span class="text-sm text-gray-600 inline-flex items-center">
                                    Amount Received Now:
                                    <x-help-tooltip>For a Cash sale, this must equal the Grand Total exactly - Cash sales can't be confirmed with a partial amount. On Credit, enter less than the total (or leave blank) to record it as Partial or unpaid; the rest goes to the customer's account balance.</x-help-tooltip>
                                </span>
                                <type="number" name="amount_received" x-model="amountReceived" min="0" :max="grandTotal" class="w-24 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg" placeholder="0.00">
                                <button type="button" @click="amountReceived = grandTotal.toFixed(2)" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 whitespace-nowrap">Pay in Full</button>
                            </div>
                            <p class="text-xs text-gray-500 text-right">Leave blank for no payment yet - status becomes Paid/Partial automatically based on what's entered here.</p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sub_total" x-bind:value="subTotal">

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200"><i class="fas fa-save mr-1"></i> Create Sale</button>
                    <a href="{{ route('admin.sales.index') }}" class="w-full sm:w-auto px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200 text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function saleForm() {
    const cashTiers = @json($commissionPreview['cash_tiers']);
    const creditRate = @json($commissionPreview['credit_rate']);
    const agentMtdCash = @json($commissionPreview['agent_mtd_cash']);
    const allProducts = @json($productsForJs);

    return {
        items: [],
        customer_id: '',
        agent_id: '',
        payment_term: 'cash',
        priceField: 'sale_price',
        discount: 0,
        discountType: 'fixed',
        tax: 0,
        shipping: 0,
        amountReceived: '',
        subTotal: 0,
        discountAmount: 0,
        commissionAmount: 0,
        commissionNote: '',
        grandTotal: 0,
        confirmBelowCost: false,
        allProducts: allProducts,

        init() { this.addRow(); },

        onCustomerChange(event) {
            const option = event.target.selectedOptions[0];
            this.priceField = (option && option.dataset.priceField) ? option.dataset.priceField : 'sale_price';

            // Re-validate existing rows against the newly selected
            // customer's group: a product not sold to that group gets
            // cleared, everything else re-prices to the group's price field.
            this.items.forEach((item, index) => {
                if (!item.product_id) return;
                const product = this.allProducts.find(p => p.id == item.product_id);
                const stillAllowed = product && (this.priceField === 'wholesale_price' ? product.is_wholesale : product.is_retail);
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

        // Products sellable to the current customer's group. Always keeps
        // a row's own already-selected product in the list too, so an
        // existing selection never silently disappears from its dropdown.
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
            const item = this.items[index];
            const product = this.allProducts.find(p => p.id == item.product_id);
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
            const item = this.items[index];
            const qty = parseFloat(item.quantity) || 0;
            const price = parseFloat(item.unit_price) || 0;
            const disc = parseFloat(item.discount) || 0;
            const tax = parseFloat(item.tax) || 0;
            item.total = (qty * price) - disc + tax;
            this.calculateTotals();
        },

        rateForCumulative(cumulative, tiers) {
            for (const t of tiers) {
                if (t.to === null || t.to === undefined || cumulative <= t.to) {
                    return parseFloat(t.rate);
                }
            }
            return tiers.length ? parseFloat(tiers[tiers.length - 1].rate) : 0;
        },

        calculateTotals() {
            this.subTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);

            const discount = parseFloat(this.discount) || 0;
            this.discountAmount = this.discountType === 'percentage' ? (this.subTotal * discount / 100) : discount;

            const tax = parseFloat(this.tax) || 0;
            const shipping = parseFloat(this.shipping) || 0;

            const netTotal = this.subTotal - this.discountAmount + tax + shipping;

            if (this.agent_id && this.payment_term === 'cash') {
                const mtd = parseFloat(agentMtdCash[this.agent_id]) || 0;
                const cumulative = mtd + netTotal;
                const rate = this.rateForCumulative(cumulative, cashTiers);
                this.commissionAmount = netTotal * rate / 100;
                this.commissionNote = rate + '% bracket (month-to-date cash: Rs. ' + mtd.toFixed(0) + ')';
            } else if (this.agent_id && this.payment_term === 'credit') {
                this.commissionAmount = netTotal * creditRate / 100;
                this.commissionNote = 'Estimate if fully recovered - credit sales accrue ' + creditRate + '% per payment received, not upfront.';
            } else {
                this.commissionAmount = 0;
                this.commissionNote = '';
            }

            // Commission is an internal cost paid to the agent, not
            // something added to what the customer owes.
            this.grandTotal = netTotal;
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

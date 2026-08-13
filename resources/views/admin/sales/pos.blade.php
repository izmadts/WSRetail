@extends('layouts.pos')

@section('title', 'POS')

@section('content')
<div x-data="posForm()" class="space-y-4">

    <!-- Header bar: customer / location / payment term -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" x-model="customer_id" @change="onCustomerChange()" required class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">
                        {{ $customer->name }} ({{ $customer->code }})@if($customer->customerGroup) - {{ $customer->customerGroup->name }}@endif
                    </option>
                    @endforeach
                </select>
                <a href="{{ route('admin.customers.create') }}" target="_blank" class="mt-1 inline-block text-xs text-blue-600 hover:underline"><i class="fas fa-plus mr-1"></i>New customer</a>
            </div>

            @if($lockedLocation)
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                <div class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700">
                    <i class="fas fa-lock text-gray-400 mr-1"></i>{{ $lockedLocation->name }} ({{ ucfirst($lockedLocation->pos_type) }})
                </div>
                <p class="mt-1 text-xs text-gray-500" x-show="locationType && locationType !== 'both'" x-text="'Locked to ' + locationType"></p>
            </div>
            @elseif($locations->isNotEmpty())
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    Location
                    <x-help-tooltip>A location locked to Retail or Wholesale (set in Settings &gt; Locations) forces this sale's products/pricing accordingly, overriding the customer's group.</x-help-tooltip>
                </label>
                <select name="location_id" x-model="location_id" @change="onLocationChange($event)" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">No specific location</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" data-pos-type="{{ $loc->pos_type }}" {{ ($posSettings->default_location_id ?? null) == $loc->id ? 'selected' : '' }}>{{ $loc->name }} ({{ ucfirst($loc->pos_type) }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500" x-show="locationType && locationType !== 'both'" x-text="'Locked to ' + locationType"></p>
            </div>
            @elseif(auth()->user() && auth()->user()->isAdmin())
            <div class="text-xs text-gray-500 self-end pb-2">
                No POS locations set up yet. <a href="{{ route('admin.settings.locations.index') }}" class="text-blue-600 hover:underline">Configure in Settings &gt; Locations</a>.
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment</label>
                <div class="flex rounded-lg border border-gray-300 overflow-hidden text-sm">
                    <button type="button" @click="setPaymentTerm('cash')" :class="payment_term === 'cash' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" class="flex-1 py-1.5 transition-colors duration-150">Cash</button>
                    <button type="button" @click="setPaymentTerm('credit')" :class="payment_term === 'credit' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'" class="flex-1 py-1.5 transition-colors duration-150">Credit</button>
                </div>
                @if(count($posSettings->payment_methods ?? ['cash']) > 1)
                <select x-model="paymentMethod" x-show="payment_term === 'cash'" x-cloak class="mt-1 w-full px-2 py-1 text-xs border border-gray-300 rounded-lg">
                    @foreach($posSettings->payment_methods ?? ['cash'] as $method)
                    <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            <div class="flex items-end">
                <button type="button" @click="showMore = !showMore" class="text-sm text-blue-600 hover:underline">
                    <i class="fas" :class="showMore ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    <span x-text="showMore ? 'Fewer options' : 'More options (discount, tax, notes)'"></span>
                </button>
            </div>
        </div>

        <div x-show="showMore" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3 pt-3 border-t border-gray-100">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Discount</label>
                <div class="flex gap-1">
                    <input type="number" name="discount" x-model="discount" @input="calculateTotals()" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg" min="0" step="0.01">
                    <select name="discount_type" x-model="discountType" @change="calculateTotals()" class="w-16 px-1 py-1 text-xs border border-gray-300 rounded-lg"><option value="fixed">Rs</option><option value="percentage">%</option></select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tax</label>
                <input type="number" name="tax" x-model="tax" @input="calculateTotals()" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg" min="0" step="0.01">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Shipping</label>
                <input type="number" name="shipping_cost" x-model="shipping" @input="calculateTotals()" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg" min="0" step="0.01">
            </div>
            <div class="lg:col-span-4">
                <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" name="notes" placeholder="Add notes..." class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Product grid -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-card p-4">
            <div class="flex flex-col sm:flex-row gap-2 mb-3">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" x-model="productSearch" @input="resetProductsShown()" placeholder="Search product name, code, or barcode..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-3">
                <button type="button" @click="activeCategory = null; resetProductsShown()" :class="activeCategory === null ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1 text-xs rounded-full transition-colors duration-150">All</button>
                @foreach($categories as $cat)
                <button type="button" @click="activeCategory = {{ $cat->id }}; resetProductsShown()" :class="activeCategory === {{ $cat->id }} ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1 text-xs rounded-full transition-colors duration-150">{{ $cat->name }}</button>
                @endforeach
            </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[65vh] overflow-y-auto pr-1">
                <template x-for="p in visibleProductsPage()" :key="p.id">
                    <button type="button" @click="onTileClick(p.id)" class="text-left border border-gray-200 rounded-xl p-3 hover:border-blue-400 hover:shadow-md transition-all duration-150 bg-white relative">
                        <span x-show="p.has_variants" class="absolute top-2 right-2 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[10px] font-medium">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div class="h-16 flex items-center justify-center mb-2 bg-gray-50 rounded-lg overflow-hidden">
                            <template x-if="p.image"><img :src="p.image" class="h-full w-full object-cover"></template>
                            <template x-if="!p.image"><i class="fas fa-box text-2xl text-gray-300"></i></template>
                        </div>
                        <p class="text-sm font-medium text-gray-900 line-clamp-2" x-text="p.name"></p>
                        <p class="text-xs text-gray-400" x-text="p.code"></p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-sm font-semibold text-blue-600" x-text="priceLabelFor(p)"></span>
                            <span class="text-xs text-gray-400" x-text="'Stk: ' + stockLabelFor(p)"></span>
                        </div>
                    </button>
                </template>
                <p x-show="visibleProducts().length === 0" class="col-span-full text-center py-10 text-gray-400 text-sm">No products match.</p>
            </div>

            <!-- Variant picker -->
            <div x-show="variantPickerProduct" x-cloak @click.self="variantPickerProduct = null"
                 class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-4" @click.outside="variantPickerProduct = null">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-900" x-text="variantPickerProduct?.name"></h4>
                        <button type="button" @click="variantPickerProduct = null" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="space-y-1.5 max-h-80 overflow-y-auto">
                        <template x-for="v in (variantPickerProduct?.variants || [])" :key="v.id">
                            <button type="button" @click="addToCart(variantPickerProduct.id, v.id)"
                                    :disabled="v.current_stock <= 0"
                                    class="w-full flex items-center justify-between px-3 py-2 border border-gray-200 rounded-lg text-sm hover:border-blue-400 hover:bg-blue-50 disabled:opacity-40 disabled:cursor-not-allowed">
                                <span x-text="v.label"></span>
                                <span class="text-gray-500 text-xs">
                                    <span x-text="'Rs. ' + priceFor(v).toFixed(2)"></span>
                                    &middot; <span x-text="'Stk: ' + v.current_stock"></span>
                                </span>
                            </button>
                        </template>
                        <p x-show="!(variantPickerProduct?.variants || []).length" class="text-center text-gray-400 text-sm py-4">No variants available.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3" x-show="visibleProducts().length > visibleProductsPage().length">
                <button type="button" @click="productsShown += {{ (int) ($posSettings->products_per_page ?? 24) }}" class="px-4 py-1.5 text-sm text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                    Load more (<span x-text="visibleProducts().length - visibleProductsPage().length"></span> more)
                </button>
            </div>
        </div>

        <!-- Cart -->
        <div class="bg-white rounded-xl shadow-card p-4 flex flex-col lg:sticky lg:top-20 lg:max-h-[calc(100vh-6rem)]">
            <h4 class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-shopping-cart text-gray-400 mr-1"></i> Cart <span class="text-gray-400" x-text="'(' + items.length + ')'"></span></h4>

            <div class="flex-1 overflow-y-auto space-y-2 mb-3" style="min-height: 100px;">
                <template x-for="(item, index) in items" :key="item.product_id + '_' + (item.product_variant_id || 0)">
                    <div class="border border-gray-100 rounded-lg p-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="item.name"></p>
                                <p class="text-xs" x-show="stockWarning(item)"><span class="text-orange-600"><i class="fas fa-exclamation-triangle mr-1"></i>Only <span x-text="item.stock"></span> in stock</span></p>
                                <p class="text-xs" x-show="belowCost(item)"><span class="text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>Below cost</span></p>
                            </div>
                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="flex items-center gap-2 mt-1.5">
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button type="button" @click="decrementQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-l-lg">-</button>
                                <input type="number" x-model="item.quantity" @input="calculateRow(index)" class="w-12 text-center text-sm border-0 focus:ring-0" min="0.01" step="0.01">
                                <button type="button" @click="incrementQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-r-lg">+</button>
                            </div>
                            <input type="number" x-model="item.unit_price" @input="calculateRow(index)" class="w-20 px-1 py-1 text-sm text-right border border-gray-300 rounded-lg" :class="belowCost(item) ? 'border-red-400 bg-red-50' : ''" min="0" step="0.01">
                            <span class="ml-auto text-sm font-semibold text-blue-600" x-text="'Rs. ' + item.total.toFixed(2)"></span>
                        </div>
                    </div>
                </template>
                <p x-show="items.length === 0" class="text-center py-8 text-gray-400 text-sm"><i class="fas fa-box-open text-2xl mb-2 block"></i>Cart is empty. Click a product to add it.</p>
            </div>

            <!-- Below-cost confirmation gate -->
            <div x-show="hasBelowCost()" x-cloak class="mb-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                <label class="flex items-start gap-2 text-xs text-red-800">
                    <input type="checkbox" x-model="confirmBelowCost" class="mt-0.5 h-4 w-4 text-red-600 rounded">
                    <span>One or more items are below purchase cost. Confirm to proceed.</span>
                </label>
            </div>

            <div class="border-t border-gray-200 pt-2 space-y-1">
                <div class="flex items-center justify-between text-sm" x-show="discountAmount > 0"><span class="text-gray-500">Discount</span><span class="text-red-600" x-text="'- Rs. ' + discountAmount.toFixed(2)"></span></div>
                <div class="flex items-center justify-between text-sm" x-show="parseFloat(tax) > 0"><span class="text-gray-500">Tax</span><span x-text="'Rs. ' + parseFloat(tax || 0).toFixed(2)"></span></div>
                <div class="flex items-center justify-between text-sm" x-show="parseFloat(shipping) > 0"><span class="text-gray-500">Shipping</span><span x-text="'Rs. ' + parseFloat(shipping || 0).toFixed(2)"></span></div>
                <div class="flex items-center justify-between bg-blue-50 rounded-lg px-2 py-2 mt-1">
                    <span class="font-bold text-gray-700">Total</span>
                    <span class="text-lg font-bold text-blue-600" x-text="'Rs. ' + grandTotal.toFixed(2)"></span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1" x-show="payment_term === 'credit'">
                    <span class="text-gray-500">Received</span>
                    <input type="number" x-model="amountReceived" min="0" :max="grandTotal" class="w-24 px-2 py-1 text-sm text-right border border-gray-300 rounded-lg" placeholder="0.00">
                </div>
            </div>

            <button type="button" @click="submitSale()" :disabled="items.length === 0 || !customer_id" :class="(items.length === 0 || !customer_id) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'" class="mt-3 w-full py-2.5 bg-green-600 text-white rounded-lg font-semibold transition-colors duration-150">
                <i class="fas fa-check mr-1"></i> Complete Sale
            </button>
        </div>
    </div>

    <!-- Real form, submitted programmatically by submitSale() so the cart's
         hidden inputs above (rendered via x-for) are guaranteed to be in the
         DOM at submit time. -->
    <form id="pos-sale-form" action="{{ route('admin.sales.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="customer_id" x-bind:value="customer_id">
        <input type="hidden" name="location_id" x-bind:value="location_id">
        <input type="hidden" name="sale_date" value="{{ date('Y-m-d') }}">
        <input type="hidden" name="payment_term" x-bind:value="payment_term">
        <input type="hidden" name="payment_method" x-bind:value="payment_term === 'cash' ? paymentMethod : 'cash'">
        <input type="hidden" name="status" value="confirmed">
        <input type="hidden" name="redirect_to" value="receipt">
        <input type="hidden" name="sub_total" x-bind:value="subTotal">
        <input type="hidden" name="discount" x-bind:value="discount">
        <input type="hidden" name="discount_type" x-bind:value="discountType">
        <input type="hidden" name="tax" x-bind:value="tax">
        <input type="hidden" name="shipping_cost" x-bind:value="shipping">
        <input type="hidden" name="amount_received" x-bind:value="amountReceived">
    </form>
</div>

<script>
function posForm() {
    const allProducts = @json($productsForJs);
    const customerPriceFields = @json($customers->mapWithKeys(fn ($c) => [$c->id => $c->customerGroup->price_field ?? 'sale_price']));

    return {
        items: [],
        customer_id: {{ $posSettings->default_customer_id ?? 'null' }} || '',
        location_id: {{ $lockedLocation->id ?? ($posSettings->default_location_id ?? 'null') }} || '',
        locationType: '{{ $lockedLocation->pos_type ?? '' }}',
        customerPriceField: 'sale_price',
        priceField: 'sale_price',
        payment_term: 'cash',
        paymentMethod: 'cash',
        discount: 0,
        discountType: 'fixed',
        tax: 0,
        shipping: 0,
        amountReceived: 0,
        subTotal: 0,
        discountAmount: 0,
        grandTotal: 0,
        confirmBelowCost: false,
        showMore: false,
        productSearch: '',
        activeCategory: null,
        productsShown: {{ (int) ($posSettings->products_per_page ?? 24) }},
        allProducts: allProducts,
        variantPickerProduct: null,

        resetProductsShown() {
            this.productsShown = {{ (int) ($posSettings->products_per_page ?? 24) }};
        },

        init() {
            // Picks up a locked/default location or default customer set
            // server-side without needing an actual @change event -
            // refreshPriceField() drives the same revalidation either way.
            if (this.customer_id) this.customerPriceField = customerPriceFields[this.customer_id] || 'sale_price';
            if (this.location_id || this.customer_id) this.refreshPriceField();
        },

        onCustomerChange() {
            this.customerPriceField = customerPriceFields[this.customer_id] || 'sale_price';
            this.refreshPriceField();
        },

        onLocationChange(event) {
            const option = event.target.selectedOptions[0];
            this.locationType = (option && option.dataset.posType) ? option.dataset.posType : '';
            this.refreshPriceField();
        },

        // Same override rule as the regular sale form: a Retail/Wholesale-
        // locked location forces that price field regardless of the
        // customer's group; "Both"/no location falls back to the group.
        refreshPriceField() {
            this.priceField = this.locationType === 'retail' ? 'sale_price'
                : this.locationType === 'wholesale' ? 'wholesale_price'
                : this.customerPriceField;

            // Drop any cart item no longer sellable under the new price
            // field, re-price the rest.
            this.items = this.items.filter(item => {
                const product = this.allProducts.find(p => p.id == item.product_id);
                return product && (this.priceField === 'wholesale_price' ? product.is_wholesale : product.is_retail);
            });
            this.items.forEach((item, index) => {
                const product = this.allProducts.find(p => p.id == item.product_id);
                const variant = item.product_variant_id ? product.variants.find(v => v.id == item.product_variant_id) : null;
                item.unit_price = this.priceFor(variant || product);
                this.calculateRow(index);
            });
            this.calculateTotals();
        },

        priceFor(productOrVariant) {
            return this.priceField === 'wholesale_price' ? productOrVariant.wholesale_price : productOrVariant.sale_price;
        },

        // Grid tiles for a variant product show a price range (its
        // variants can be priced differently) instead of one number.
        priceLabelFor(p) {
            if (!p.has_variants) return 'Rs. ' + this.priceFor(p).toFixed(2);
            if (!p.variants.length) return 'Rs. 0.00';
            const prices = p.variants.map(v => this.priceFor(v));
            const min = Math.min(...prices), max = Math.max(...prices);
            return min === max ? 'Rs. ' + min.toFixed(2) : 'Rs. ' + min.toFixed(2) + '–' + max.toFixed(2);
        },

        stockLabelFor(p) {
            if (!p.has_variants) return p.current_stock;
            return p.variants.reduce((sum, v) => sum + (parseFloat(v.current_stock) || 0), 0);
        },

        setPaymentTerm(term) {
            this.payment_term = term;
            if (term === 'cash') this.amountReceived = this.grandTotal;
            this.calculateTotals();
        },

        visibleProducts() {
            const search = this.productSearch.trim().toLowerCase();
            return this.allProducts.filter(p => {
                if (this.priceField === 'wholesale_price' ? !p.is_wholesale : !p.is_retail) return false;
                if (this.activeCategory !== null && p.category_id != this.activeCategory) return false;
                if (search && !(p.name.toLowerCase().includes(search) || (p.code || '').toLowerCase().includes(search))) return false;
                return true;
            });
        },

        // Settings > POS Settings' "Products Shown Per Page" - the grid
        // only renders this many at a time, with a "Load more" button to
        // reveal the rest, instead of rendering (and re-filtering) every
        // product on every keystroke.
        visibleProductsPage() {
            return this.visibleProducts().slice(0, this.productsShown);
        },

        // A variant product opens the picker instead of adding straight to
        // cart - which exact variant (and its price/stock) can't be decided
        // without one.
        onTileClick(productId) {
            const product = this.allProducts.find(p => p.id == productId);
            if (!product) return;
            if (product.has_variants) {
                this.variantPickerProduct = product;
            } else {
                this.addToCart(productId, null);
            }
        },

        addToCart(productId, variantId = null) {
            const product = this.allProducts.find(p => p.id == productId);
            if (!product) return;
            const variant = variantId ? product.variants.find(v => v.id == variantId) : null;
            if (variantId && !variant) return;

            const existing = this.items.find(i => i.product_id == productId && (i.product_variant_id || null) == (variantId || null));
            if (existing) {
                existing.quantity = (parseFloat(existing.quantity) || 0) + 1;
                this.calculateRow(this.items.indexOf(existing));
                this.variantPickerProduct = null;
                return;
            }

            const priceSource = variant || product;
            this.items.push({
                product_id: product.id,
                product_variant_id: variantId,
                name: variant ? `${product.name} (${variant.label})` : product.name,
                quantity: 1,
                unit_price: this.priceFor(priceSource),
                total: this.priceFor(priceSource),
                stock: priceSource.current_stock,
                cost: priceSource.purchase_price,
            });
            this.calculateTotals();
            this.variantPickerProduct = null;
        },

        incrementQty(index) {
            this.items[index].quantity = (parseFloat(this.items[index].quantity) || 0) + 1;
            this.calculateRow(index);
        },

        decrementQty(index) {
            const next = (parseFloat(this.items[index].quantity) || 0) - 1;
            if (next <= 0) { this.removeItem(index); return; }
            this.items[index].quantity = next;
            this.calculateRow(index);
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.calculateTotals();
        },

        stockWarning(item) {
            return item.stock !== null && (parseFloat(item.quantity) || 0) > item.stock;
        },

        belowCost(item) {
            if (!item.cost || item.cost <= 0) return false;
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
            item.total = qty * price;
            this.calculateTotals();
        },

        calculateTotals() {
            this.subTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);

            const discount = parseFloat(this.discount) || 0;
            this.discountAmount = this.discountType === 'percentage' ? (this.subTotal * discount / 100) : discount;

            const tax = parseFloat(this.tax) || 0;
            const shipping = parseFloat(this.shipping) || 0;
            this.grandTotal = this.subTotal - this.discountAmount + tax + shipping;

            if (this.payment_term === 'cash') this.amountReceived = this.grandTotal;
        },

        submitSale() {
            if (this.items.length === 0 || !this.customer_id) return;
            if (this.hasBelowCost() && !this.confirmBelowCost) {
                alert('One or more items are priced below cost. Please confirm the below-cost checkbox before submitting.');
                return;
            }
            if (this.hasStockWarning() && !confirm('One or more items exceed available stock. Submit anyway?')) {
                return;
            }

            // The cart lives in Alpine state, not inside <form>#pos-sale-form
            // (it needs to sit next to the product grid, not hidden away) -
            // build the items[] hidden inputs the regular sale form would
            // have had, right before submitting.
            const form = document.getElementById('pos-sale-form');
            form.querySelectorAll('input[name^="items["]').forEach(el => el.remove());
            this.items.forEach((item, index) => {
                const fields = { product_id: item.product_id, product_variant_id: item.product_variant_id || '', quantity: item.quantity, unit_price: item.unit_price, discount: 0, tax: 0 };
                Object.entries(fields).forEach(([field, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `items[${index}][${field}]`;
                    input.value = value;
                    form.appendChild(input);
                });
            });

            form.submit();
        }
    };
}
</script>
@endsection

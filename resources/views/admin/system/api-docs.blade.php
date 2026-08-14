@extends('layouts.admin')

@section('title', 'API Documentation')
@section('page-title', 'Customer API Documentation')

@section('content')
<div>
    <div class="flex flex-col lg:flex-row gap-6">

    <!-- Table of Contents -->
    <div class="lg:w-64 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-card p-4 lg:sticky lg:top-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Contents</p>
            <nav class="space-y-1 text-sm">
                <a href="#c-getting-started" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-rocket w-4 mr-1 text-gray-400"></i> Getting Started</a>
                <a href="#c-authentication" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-key w-4 mr-1 text-gray-400"></i> Connect &amp; Auth</a>
                <a href="#c-response-format" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-code w-4 mr-1 text-gray-400"></i> Response Format</a>
                <a href="#c-profile" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-id-card w-4 mr-1 text-gray-400"></i> Profile</a>
                <a href="#c-categories" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-th-large w-4 mr-1 text-gray-400"></i> Categories</a>
                <a href="#c-products" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-box w-4 mr-1 text-gray-400"></i> Products</a>
                <a href="#c-payment-methods" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-credit-card w-4 mr-1 text-gray-400"></i> Payment Methods</a>
                <a href="#c-orders" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-shopping-cart w-4 mr-1 text-gray-400"></i> Orders</a>
                <a href="#c-redirects" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-route w-4 mr-1 text-gray-400"></i> SEO Redirects</a>
                <a href="#c-errors" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-exclamation-triangle w-4 mr-1 text-gray-400"></i> Error Reference</a>
                <a href="#c-integration" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-plug w-4 mr-1 text-gray-400"></i> Integration Guide</a>
            </nav>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.system.api.tester') }}" class="block text-center px-3 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
                    <i class="fas fa-flask mr-1"></i> Open API Tester
                </a>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0 space-y-6">

        {{-- ============================================================ --}}
        {{-- GETTING STARTED --}}
        {{-- ============================================================ --}}
        <section id="c-getting-started" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-rocket text-purple-600 mr-2"></i> Getting Started</h2>
            <p class="text-sm text-gray-600 mb-4">
                This is the REST API your <strong>storefront</strong> (e.g. the companion Next.js storefront
                app) calls to let shoppers - who are <code>Customer</code> records in this system - view their
                profile, browse the catalog, and place orders, without ever leaving your app. This is a
                <strong>system-to-system integration</strong>, not a public sign-up flow: there is no password
                screen. Your storefront's backend already owns identity/verification for its users (e.g. a
                name + phone number captured at checkout); this API trusts that.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Base URL</p>
                <code class="text-sm text-purple-700 font-mono">{{ url('/api/v1/customer') }}</code>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3 bg-purple-50 rounded-lg">
                    <p class="text-xs font-semibold text-purple-700 uppercase mb-1">Format</p>
                    <p class="text-sm text-gray-700">JSON in, JSON out. Send <code>Content-Type: application/json</code> and <code>Accept: application/json</code> on every request.</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs font-semibold text-blue-700 uppercase mb-1">Auth</p>
                    <p class="text-sm text-gray-700">Bearer token (Laravel Sanctum), one token per seller/customer - obtained from <code>/connect</code>, not from a login form.</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-xs font-semibold text-green-700 uppercase mb-1">Versioning</p>
                    <p class="text-sm text-gray-700">Path-versioned (<code>/v1/</code>). Breaking changes ship under a new version, never in place.</p>
                </div>
            </div>

            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800"><i class="fas fa-lightbulb mr-1"></i> <strong>Pricing: always retail, unconditionally.</strong>
                This entire API is a retail-only channel - every product listing, price, and order placed through
                it uses <code>sale_price</code> / <code>is_retail</code>, full stop. This is <strong>not</strong>
                conditional on the customer's <code>customer_group</code> (that field still exists and still defaults
                to "Retail" at <code>/connect</code>, and is still useful for reporting/reconciliation elsewhere in
                WSRetail) - even if an admin later changes a customer's group for some other purpose, their
                storefront orders still price at retail. Price is always resolved server-side from the product
                record, never trusted from a request body.</p>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- CONNECT & AUTH --}}
        {{-- ============================================================ --}}
        <section id="c-authentication" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-key text-purple-600 mr-2"></i> Connect &amp; Authentication</h2>
            <p class="text-sm text-gray-600 mb-4">
                <code>/connect</code> is the only endpoint that isn't gated by a customer's own token - it's gated by a
                <strong>shared integration key</strong> instead, since it hands out a token for whatever phone number
                it's given and there's no password to check.
            </p>

            <div class="p-4 bg-red-50 border border-red-200 rounded-lg mb-4">
                <p class="text-sm text-red-800"><i class="fas fa-server mr-1"></i> <strong>Must be called server-to-server, never from the browser.</strong>
                If the storefront calls its own backend directly (as it already does for everything else - see the
                Integration Guide below), that backend's server must be the one calling <code>/connect</code>, holding
                the integration key in its own server-side config. A key shipped inside browser-bundled JS is
                extractable and is not real security - do not call this endpoint from client-side code.</p>
            </div>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'POST', 'path' => '/connect', 'auth' => false,
                'description' => 'Creates a new customer, or matches an existing one BY PHONE NUMBER, then issues a Sanctum token for that one customer. Requires the X-Integration-Key header - see below. Safe to call again for the same phone number later (e.g. app reinstall): it just re-links the profile and issues a fresh token.',
                'body' => [
                    'name' => 'string, required',
                    'business_name' => 'string, optional - shop/business name',
                    'phone' => 'string, required - the matching/identity key. Use a consistent format (e.g. always with or without a country code) since matching is an exact string comparison.',
                    'mobile' => 'string, optional', 'email' => 'string, optional',
                    'address' => 'string, optional', 'city' => 'string, optional', 'gps_location' => 'string, optional',
                    'cnic' => 'string, optional', 'ntn' => 'string, optional',
                    'device_name' => 'string, optional - label for this token',
                ],
                'response' => <<<'JSON'
{
  "success": true,
  "message": "Connected successfully.",
  "data": {
    "token": "7|abcdef1234567890...",
    "token_type": "Bearer",
    "customer": {
      "id": 41, "code": "IZM-KAR-00041", "name": "Bilal Traders", "business_name": "Bilal Traders",
      "phone": "03001234567", "order_channel": "retail",
      "...": "..."
    }
  }
}
JSON,
            ])

            <div class="p-4 bg-red-50 border border-red-200 rounded-lg mb-3">
                <p class="text-sm text-red-800"><i class="fas fa-shield-alt mr-1"></i> <strong>X-Integration-Key header:</strong>
                every call to <code>/connect</code> must include this header with your shared secret
                (configured server-side as <code>CUSTOMER_API_INTEGRATION_KEY</code>). A missing or wrong key returns
                <code>401</code> before anything else is checked. Ask an admin for the current value - it is not
                shown here since this documentation page isn't itself a secured secret vault.</p>
            </div>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'POST', 'path' => '/logout', 'auth' => true,
                'description' => 'Revokes the token used to make this request.',
                'response' => '{"success": true, "message": "Logged out successfully.", "data": null}',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- RESPONSE FORMAT --}}
        {{-- ============================================================ --}}
        <section id="c-response-format" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-code text-purple-600 mr-2"></i> Response Format</h2>
            <p class="text-sm text-gray-600 mb-3">Every endpoint here returns the same envelope:</p>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Success</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>{
  "success": true,
  "message": "OK",
  "data": {},        // object, array, or null depending on the endpoint
  "meta": {}         // present only on paginated list endpoints
}</code></pre>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Error</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>{
  "success": false,
  "message": "Human-readable error description.",
  "errors": { "field_name": ["Specific validation message."] }  // present on 422 only
}</code></pre>

            <p class="text-xs text-gray-500 mt-2">
                Paginated endpoints accept <code>?page=2&per_page=20</code> and return a
                <code>current_page/last_page/per_page/total</code> block in <code>meta</code>.
            </p>
        </section>

        {{-- ============================================================ --}}
        {{-- PROFILE --}}
        {{-- ============================================================ --}}
        <section id="c-profile" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-id-card text-purple-600 mr-2"></i> Profile</h2>
            <p class="text-sm text-gray-600 mb-4">
                Full self-view: contact details, balance, and pricing channel in one response -
                "customer can see their details" in a single call.
            </p>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/me', 'auth' => true,
                'description' => 'The connected customer\'s full profile.',
                'response' => <<<'JSON'
{
  "success": true,
  "data": {
    "id": 41, "code": "IZM-KAR-00041", "name": "Bilal Traders", "business_name": "Bilal Traders",
    "phone": "03001234567", "address": "...", "city": "Karachi",
    "credit_limit": 50000.00, "balance": 12500.00, "is_active": true, "order_count": 14,
    "order_channel": "retail",
    "customer_group": {"id": 1, "name": "Retail", "price_field": "sale_price"}
  }
}
JSON,
            ])

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'PUT', 'path' => '/me', 'auth' => true,
                'description' => 'Update profile fields. Phone is deliberately NOT editable here - it is the /connect matching key, so changing it goes through an admin instead.',
                'body' => [
                    'name' => 'string, required', 'business_name' => 'string, optional',
                    'email' => 'string, optional', 'mobile' => 'string, optional',
                    'address' => 'string, optional', 'city' => 'string, optional', 'state' => 'string, optional',
                    'gps_location' => 'string, optional',
                ],
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- CATEGORIES --}}
        {{-- ============================================================ --}}
        <section id="c-categories" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-th-large text-purple-600 mr-2"></i> Categories</h2>
            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/categories', 'auth' => true,
                'description' => 'Active categories that have at least one in-stock product available for this customer\'s pricing channel - a category with nothing orderable in it is left out. No images (the Category model doesn\'t have one) - render with a generic icon client-side.',
                'response' => '{"success": true, "data": [ {"id": 3, "name": "Spices"}, {"id": 5, "name": "Groceries"} ]}',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- PRODUCTS --}}
        {{-- ============================================================ --}}
        <section id="c-products" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-box text-purple-600 mr-2"></i> Products</h2>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/products', 'auth' => true,
                'description' => 'Active, in-stock, retail-eligible catalog, already priced at sale_price - this API is a retail-only channel, unconditionally (see the pricing note above). The "price" field shown is what an order will actually charge - no need to compute it client-side.',
                'query' => ['search' => 'string, optional - matches name/code', 'category_id' => 'integer, optional - from GET /categories'],
                'response' => <<<'JSON'
{
  "success": true,
  "data": [
    {
      "id": 7, "code": "PRD-A1B2", "name": "Cooking Oil 5L", "category_id": 5, "category": "Groceries",
      "description": "Refined cooking oil, 5 litre bottle.", "short_description": "5L cooking oil.",
      "brand": null, "unit": "piece",
      "sale_price": 1450.00, "wholesale_price": 1300.00, "current_stock": 84.00,
      "weight": 5.2, "length": 15.0, "width": 15.0, "height": 25.0,
      "is_retail": true, "is_wholesale": true,
      "image": "uploads/products/abc123.jpg", "images": ["uploads/products/abc123.jpg", "uploads/products/abc124.jpg"],
      "price": 1450.00, "price_field": "sale_price"
    }
  ]
}
JSON,
            ])
            <p class="text-xs text-gray-500 mt-2">
                <code>image</code> is a relative path, or <code>null</code> if the product has none - build the full
                URL as <code>{{ url('/') }}/&lt;image&gt;</code> (it's served directly from <code>public/</code>, not
                behind a <code>/storage/</code> prefix).
            </p>
        </section>

        {{-- ============================================================ --}}
        {{-- PAYMENT METHODS --}}
        {{-- ============================================================ --}}
        <section id="c-payment-methods" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-credit-card text-purple-600 mr-2"></i> Payment Methods</h2>
            <p class="text-sm text-gray-600 mb-4">
                Checkout options configured in <strong>Settings &gt; Ecommerce &gt; Payment</strong> - not a payment
                gateway list. Render these as the choices at checkout; whichever one the shopper picks is sent back
                as free text in <code>payment_method</code> on <code>POST /orders</code> below.
            </p>
            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/payment-methods', 'auth' => true,
                'description' => 'Only enabled methods, in admin-configured order.',
                'response' => <<<'JSON'
{
  "success": true,
  "data": [
    { "id": 1, "code": "cod", "name": "Cash on Delivery", "description": "Pay in cash when your order arrives." }
  ]
}
JSON,
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- ORDERS --}}
        {{-- ============================================================ --}}
        <section id="c-orders" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-shopping-cart text-purple-600 mr-2"></i> Orders</h2>
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-1"></i>
                An order placed here does <strong>not</strong> immediately move stock or post accounting - it's
                created as a <strong>pending order</strong> (internally a normal Sale, <code>status: "draft"</code>,
                <code>source: "customer_app"</code>). It only becomes real - stock deducted, ledger posted - once an
                admin confirms it via the admin panel.
                This protects against a public-facing app directly committing real stock/accounting with no review.
                </p>
            </div>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/orders', 'auth' => true,
                'description' => 'This customer\'s own orders, paginated.',
                'query' => ['status' => 'draft|confirmed|partial|paid|cancelled, optional'],
            ])

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'POST', 'path' => '/orders', 'auth' => true,
                'description' => 'Place an order. Send product_id + quantity only - unit_price is always resolved server-side from the product\'s sale_price, never trusted from the request. Rejected with 422 if a product isn\'t available for retail or requested quantity exceeds current stock. shipping_cost is computed server-side too, from Settings > Ecommerce > Shipping (a flat rate, waived once the subtotal reaches the free-shipping threshold) - it is not sent in the request body.',
                'body' => [
                    'sale_date' => 'date, required',
                    'payment_term' => 'in: cash,credit - required. The customer\'s own choice at checkout (e.g. "Cash on Delivery" vs an account/credit order) - does NOT mean payment has already happened, it only sets the term the confirming admin will collect against.',
                    'payment_method' => 'string, optional - free text (e.g. "Cash on Delivery", "Card"). The actual checkout method, distinct from payment_term above.',
                    'notes' => 'string, optional',
                    'shipping_address' => 'object, optional - a point-in-time snapshot for this order only, not written back to the customer\'s saved profile address. Fields: name, address_1, address_2, city, state, postcode, country, phone (all optional strings).',
                    'items' => 'array, required, min 1 item',
                    'items.*.product_id' => 'integer, required',
                    'items.*.quantity' => 'numeric, required, min 0.01',
                ],
                'response' => <<<'JSON'
{
  "success": true,
  "message": "Order placed! It is pending confirmation from the admin.",
  "data": {
    "id": 231, "invoice_no": "SA-260804-00231", "source": "customer_app", "payment_term": "credit",
    "payment_method": "Cash on Delivery", "coupon_code": null,
    "status": "draft", "status_label": "Draft",
    "sub_total": 2600.00, "total_amount": 2600.00, "paid_amount": 0, "due_amount": 2600.00,
    "shipping_address": {"name": "Ali Raza", "address_1": "12 Mall Road", "city": "Lahore", "country": "PK"},
    "items": [ {"id":501,"product_id":7,"product_name":"Cooking Oil 5L","quantity":2,"unit_price":1300,"total_price":2600} ]
  }
}
JSON,
            ])

            @include('admin.system._endpoint', ['base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/orders/{id}', 'auth' => true, 'description' => 'Single order with items and payment history.'])

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'POST', 'path' => '/orders/{id}/cancel', 'auth' => true,
                'description' => 'Cancel an order - only while it\'s still pending (status "draft"). Once an admin has confirmed it, cancelling has to go through them since stock/ledger entries now exist.',
            ])

            <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <p class="text-sm text-purple-900 mb-2"><i class="fas fa-route mr-1"></i> <strong>Displaying order status client-side:</strong>
                WSRetail only tracks payment/posting status (no delivery/logistics tracking exists). Map it for display as:</p>
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-purple-700"><th class="py-1 pr-3">WSRetail <code>status</code></th><th class="py-1">Show as</th></tr></thead>
                    <tbody class="divide-y divide-purple-100">
                        <tr><td class="py-1 pr-3 font-mono">draft</td><td class="py-1">Pending</td></tr>
                        <tr><td class="py-1 pr-3 font-mono">confirmed / partial / paid</td><td class="py-1">Delivered (the admin confirming an order is treated as fulfilling it - there's no separate "out for delivery" step today)</td></tr>
                        <tr><td class="py-1 pr-3 font-mono">cancelled</td><td class="py-1">Cancelled / Rejected</td></tr>
                    </tbody>
                </table>
                <p class="text-xs text-purple-700 mt-2">This is a client-side display convention only, not a WSRetail field. If real delivery/logistics tracking (a genuine "out for delivery" step, driver assignment, etc.) becomes a real need later, that would be a separate addition to WSRetail's Sale model - not simulated by this mapping.</p>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- SEO REDIRECTS --}}
        {{-- ============================================================ --}}
        <section id="c-redirects" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-route text-purple-600 mr-2"></i> SEO Redirects</h2>
            <p class="text-sm text-gray-600 mb-4">
                When a store migrates from another CMS (e.g. WooCommerce), its old product URLs may already rank in
                search results or be bookmarked by returning customers. Every product import automatically captures a
                mapping from the old URL to the new product's page here - see
                <strong>Settings &gt; Integrations &gt; SEO Redirects</strong> in the admin to browse or export them.
                Use this endpoint to resolve one of those old URLs at request time and issue a real permanent
                redirect, instead of letting the visitor hit a 404.
            </p>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/redirect', 'auth' => false,
                'description' => 'Public - no customer token needed, since this has to resolve for anonymous visitors who haven\'t connected yet. Throttled (120/min).',
                'query' => ['path' => 'string, required - the old URL\'s path only, e.g. "/product/red-t-shirt" (scheme/host are ignored if included).'],
                'response' => <<<'JSON'
{
  "success": true, "message": "OK",
  "data": { "old_path": "/product/red-t-shirt", "new_path": "/products/18", "product_id": 18 }
}
JSON,
            ])

            <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <p class="text-sm text-purple-900 mb-1"><i class="fas fa-lightbulb mr-1"></i> <strong>Using it in the storefront:</strong>
                the companion Next.js storefront's <code>app/product/[...slug]/page.tsx</code> catches WooCommerce's
                default permalink shape (<code>/product/%postname%/</code>), calls this endpoint server-side, and
                issues <code>permanentRedirect()</code> to the resolved <code>new_path</code> - a real HTTP redirect a
                search engine can follow, not a client-side one. If a store used a custom permalink structure (e.g.
                <code>/shop/%postname%/</code>), add a matching route the same way.</p>
                <p class="text-sm text-purple-900">Not every old URL needs the storefront in the loop at all - if the
                old domain's own web server is being retired, export a ready-to-paste rule file (.htaccess or nginx)
                from the SEO Redirects screen instead and configure the redirect at the server/CDN level.</p>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- ERROR REFERENCE --}}
        {{-- ============================================================ --}}
        <section id="c-errors" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Error Reference</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Meaning</th><th class="py-2">Typical cause</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="py-2 pr-4"><code class="text-red-600">401</code></td><td class="py-2 pr-4">Unauthenticated</td><td class="py-2">Missing/expired/revoked token on a customer route, OR a missing/wrong X-Integration-Key on /connect.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">403</code></td><td class="py-2 pr-4">Forbidden</td><td class="py-2">Customer account deactivated, or the token doesn't belong to a customer at all.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">404</code></td><td class="py-2 pr-4">Not Found</td><td class="py-2">Order/reward doesn't exist, or belongs to a different customer.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">422</code></td><td class="py-2 pr-4">Validation / business rule failure</td><td class="py-2">Bad input, insufficient stock, product not available in this channel, insufficient points, or "already processed" on cancel. Check <code>errors</code>.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">429</code></td><td class="py-2 pr-4">Too Many Requests</td><td class="py-2">/connect called more than 30 times in a minute from the same IP.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">500</code></td><td class="py-2 pr-4">Server Error</td><td class="py-2">Unexpected failure - report it, this shouldn't happen.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- INTEGRATION GUIDE --}}
        {{-- ============================================================ --}}
        <section id="c-integration" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-plug text-purple-600 mr-2"></i> Integration Guide</h2>
            <p class="text-sm text-gray-600 mb-4">
                Written for the official <strong>WSRetail storefront</strong> (a Next.js app, published separately)
                but applies to any storefront you build against this API - the contract is the same either way.
            </p>

            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-sitemap mr-1"></i> Split by who calls what</p>
                <p class="text-sm text-gray-600 mb-2">
                    Only <code>/connect</code> needs the integration key, and it's the only call that must not
                    originate from the browser. Everything after that uses the ordinary per-customer Bearer token,
                    which is safe to use directly from client-side code once issued.
                </p>
                <table class="w-full text-xs mt-2">
                    <thead><tr class="text-left text-gray-500"><th class="py-1 pr-4">Call</th><th class="py-1">Who makes it</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr><td class="py-1.5 pr-4 font-mono">POST /connect</td><td class="py-1.5">Your <strong>storefront's own server</strong> (e.g. a Next.js Route Handler such as <code>app/api/connect/route.ts</code>) - holds the integration key server-side, calls WSRetail, returns the resulting <code>token</code> back to the browser in its own response. Never ship the integration key in browser-bundled JS.</td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">Everything else<br>(/me, /products, /orders)</td><td class="py-1.5">The <strong>browser directly</strong>, using the token from step above (stored client-side, e.g. <code>localStorage</code>), sent as <code>Authorization: Bearer &lt;token&gt;</code>.</td></tr>
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-gray-600 mb-3">
                <strong>1. Connect once, on login/checkout.</strong> Capture the shopper's name and phone number
                (your storefront's own form - there is no separate signup flow), then have your server call
                <code>/connect</code> with the integration key and return just the token to the browser. Store it
                client-side and reuse it for every later call.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>2. Reconnecting is safe.</strong> If the stored token is ever missing or a call comes back
                <code>401</code>, just repeat step 1 - <code>/connect</code> matches the existing customer by phone
                rather than creating a duplicate.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>3. Checkout maps directly onto <code>POST /orders</code>.</strong> Your cart's payment choice
                (e.g. "Cash on Delivery" vs. an account/credit order) becomes <code>payment_term: "cash"</code> or
                <code>"credit"</code> (see the note on that field above - neither means payment already happened), a
                delivery address can ride along in <code>notes</code> since there's no separate delivery-address
                field on the order today, and your cart lines map directly onto <code>items[]</code>.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>4. There is no anonymous/public catalog endpoint.</strong> Every read (<code>/categories</code>,
                <code>/products</code>) requires a connected customer's token - a storefront that wants to let
                shoppers browse before providing a name/phone needs its own separate public catalog cache, or should
                connect eagerly on first visit.
            </p>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Example: your storefront's own server-side proxy (holds the integration key)</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>// Next.js Route Handler, e.g. app/api/connect/route.ts - runs on YOUR server, never in the browser
export async function POST(request: Request) {
  const { name, phone } = await request.json();

  const res = await fetch('{{ url('/api/v1/customer/connect') }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Integration-Key': process.env.WSRETAIL_INTEGRATION_KEY!, // server-only env var, never NEXT_PUBLIC_*
    },
    body: JSON.stringify({ name, phone, device_name: 'storefront' }),
  });

  const body = await res.json();
  return Response.json(body, { status: res.status });
}</code></pre>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Example: browser, everything after connect (direct to WSRetail)</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>const res = await fetch('{{ url('/api/v1/customer/orders') }}', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`,
  },
  body: JSON.stringify({
    sale_date: new Date().toISOString().split('T')[0],
    payment_term: 'cash',
    notes: deliveryAddress,
    items: cartItems.map((c) => ({ product_id: c.product.id, quantity: c.quantity })),
  }),
});</code></pre>

            <p class="text-sm text-gray-600 mt-4">
                <i class="fas fa-info-circle mr-1"></i> The <a href="{{ route('admin.system.api.tester') }}" class="text-purple-600 hover:underline font-medium">API Tester</a>
                page includes a Connect Helper that calls <code>/connect</code> with an integration key you supply,
                so you can try the whole flow (connect, browse, place an order) without curl/Postman.
            </p>
        </section>
    </div>
    </div>
    {{-- ============================================================ --}}
    {{-- END CUSTOMER API TAB --}}
    {{-- ============================================================ --}}

</div>
@endsection

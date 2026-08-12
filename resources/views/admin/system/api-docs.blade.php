@extends('layouts.admin')

@section('title', 'API Documentation')
@section('page-title', 'Sale Agent API Documentation')

@section('content')
<div x-data="{ tab: 'agent' }">

    <!-- Tab Switcher -->
    <div class="flex gap-2 mb-6 border-b border-gray-200">
        <button type="button" @click="tab = 'agent'"
            :class="tab === 'agent' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors">
            <i class="fas fa-user-tie mr-1.5"></i> Agent API
        </button>
        <button type="button" @click="tab = 'customer'"
            :class="tab === 'customer' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors">
            <i class="fas fa-store mr-1.5"></i> Customer API
        </button>
    </div>

    {{-- ================================================================ --}}
    {{-- AGENT API TAB --}}
    {{-- ================================================================ --}}
    <div x-show="tab === 'agent'" class="flex flex-col lg:flex-row gap-6">

    <!-- Table of Contents -->
    <div class="lg:w-64 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-card p-4 lg:sticky lg:top-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Contents</p>
            <nav class="space-y-1 text-sm">
                <a href="#getting-started" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-rocket w-4 mr-1 text-gray-400"></i> Getting Started</a>
                <a href="#authentication" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-key w-4 mr-1 text-gray-400"></i> Authentication</a>
                <a href="#response-format" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-code w-4 mr-1 text-gray-400"></i> Response Format</a>
                <a href="#dashboard" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-chart-pie w-4 mr-1 text-gray-400"></i> Dashboard</a>
                <a href="#customers" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-users w-4 mr-1 text-gray-400"></i> Customers</a>
                <a href="#products" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-box w-4 mr-1 text-gray-400"></i> Products</a>
                <a href="#sales" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-shopping-bag w-4 mr-1 text-gray-400"></i> Sales</a>
                <a href="#commissions" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-coins w-4 mr-1 text-gray-400"></i> Commissions</a>
                <a href="#reports" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-chart-bar w-4 mr-1 text-gray-400"></i> Reports</a>
                <a href="#errors" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-exclamation-triangle w-4 mr-1 text-gray-400"></i> Error Reference</a>
                <a href="#flutter" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600"><i class="fas fa-mobile-alt w-4 mr-1 text-gray-400"></i> Flutter Integration</a>
            </nav>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.system.api.tester') }}" class="block text-center px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
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
        <section id="getting-started" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-rocket text-blue-600 mr-2"></i> Getting Started</h2>
            <p class="text-sm text-gray-600 mb-4">
                This is the REST API that powers the Sale Agent mobile app (Flutter). It covers everything an
                agent can do on the web: dashboard, customers, products, sales, commissions, and reports.
                It does <strong>not</strong> cover admin/back-office functionality - agents only.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Base URL</p>
                <code class="text-sm text-blue-700 font-mono">{{ url('/api/v1/agent') }}</code>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs font-semibold text-blue-700 uppercase mb-1">Format</p>
                    <p class="text-sm text-gray-700">JSON in, JSON out. Send <code>Content-Type: application/json</code> and <code>Accept: application/json</code> on every request.</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg">
                    <p class="text-xs font-semibold text-purple-700 uppercase mb-1">Auth</p>
                    <p class="text-sm text-gray-700">Bearer token (Laravel Sanctum). Obtain via <code>/login</code>, send as <code>Authorization: Bearer &lt;token&gt;</code>.</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-xs font-semibold text-green-700 uppercase mb-1">Versioning</p>
                    <p class="text-sm text-gray-700">Path-versioned (<code>/v1/</code>). Breaking changes ship under a new version, never in place.</p>
                </div>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- AUTHENTICATION --}}
        {{-- ============================================================ --}}
        <section id="authentication" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-key text-blue-600 mr-2"></i> Authentication</h2>
            <p class="text-sm text-gray-600 mb-4">
                Only <strong>active, admin-approved sales_agent</strong> accounts can obtain a token - the same
                gate the web login form enforces. A token that was valid at login time stops working immediately
                if the admin later deactivates the agent (checked on every request, not just at login).
            </p>

            @include('admin.system._endpoint', [
                'method' => 'POST', 'path' => '/register', 'auth' => false,
                'description' => 'Self-service sign-up for a new sales agent. Submits as multipart/form-data (three image uploads). Does NOT return a token - the account is created pending admin approval, same as /login rejects it until approved_at is set.',
                'body' => [
                    'name' => 'string, required', 'guardian_name' => 'string, optional',
                    'email' => 'string, required, unique', 'phone_number' => 'string, required', 'whatsapp_number' => 'string, optional',
                    'address' => 'string, required', 'city' => 'string, required',
                    'cnic' => 'string, required, unique',
                    'cnic_front_image' => 'image (jpeg/png/jpg), required, max 2MB',
                    'cnic_back_image' => 'image (jpeg/png/jpg), required, max 2MB',
                    'personal_photo' => 'image (jpeg/png/jpg), required, max 2MB',
                    'payout_account_type' => 'string, required', 'payout_account_title' => 'string, required',
                    'payout_account_number' => 'string, required', 'payout_account_provider' => 'string, required',
                    'reference_name' => 'string, optional', 'reference_phone_number' => 'string, optional', 'reference_address' => 'string, optional',
                    'password' => 'string, required, min:8, must match password_confirmation', 'password_confirmation' => 'string, required',
                    'policy_accepted' => 'boolean, required, must be true',
                ],
                'response' => <<<'JSON'
{
  "success": true,
  "message": "Your application has been submitted! You will be notified once approved.",
  "data": { "id": 12, "name": "John Doe", "email": "john@example.com", "is_active": false, "approved_at": null, "...": "..." }
}
JSON,
            ])

            @include('admin.system._endpoint', [
                'method' => 'POST', 'path' => '/login', 'auth' => false,
                'description' => 'Exchange email/password for a Bearer token. Rate-limited to 6 attempts/minute per IP.',
                'body' => [
                    'email' => 'string, required',
                    'password' => 'string, required',
                    'device_name' => 'string, optional - label for this token, e.g. "John\'s Pixel 8"',
                ],
                'response' => <<<'JSON'
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "token": "1|abcdef1234567890...",
    "token_type": "Bearer",
    "agent": { "id": 5, "name": "John Doe", "email": "john@example.com", "...": "..." }
  }
}
JSON,
            ])

            @include('admin.system._endpoint', [
                'method' => 'POST', 'path' => '/logout', 'auth' => true,
                'description' => 'Revokes the token used to make this request. Other devices/tokens stay logged in.',
                'response' => '{"success": true, "message": "Logged out successfully.", "data": null}',
            ])

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/me', 'auth' => true,
                'description' => 'Current agent\'s profile, including channel (see the callout below).',
                'response' => '{"success": true, "data": {"id": 5, "name": "John Doe", "email": "...", "phone": "...", "channel": "both", "commission_rate_cash": 1.5, "...": "..."}}',
            ])

            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg mb-3">
                <p class="text-sm text-indigo-900"><i class="fas fa-shopping-basket mr-1"></i> <strong>Channel restriction:</strong>
                an agent's <code>channel</code> (set by admin at approval - "Wholesale only", "Retail only", or "Both") gates
                everything this API returns, not just what's shown here: <code>GET /products</code> only returns products
                matching the agent's channel, <code>GET /customer-groups</code> only returns the one group they're allowed to
                assign, <code>POST /customers</code> auto-assigns that group (or 422s if you explicitly submit a mismatched
                one), and <code>POST/PUT /sales</code> 403s if the selected customer's group doesn't match. <code>channel: null</code>
                (every agent created before this field existed) means unrestricted - identical to <code>"both"</code>.</p>
            </div>

            @include('admin.system._endpoint', [
                'method' => 'PUT', 'path' => '/me', 'auth' => true,
                'description' => 'Update profile fields.',
                'body' => [
                    'name' => 'string, required', 'email' => 'string, required, unique',
                    'phone' => 'string, optional', 'address' => 'string, optional', 'city' => 'string, optional',
                    'whatsapp_number' => 'string, optional',
                    'payout_account_type' => 'in: bank,easypaisa,jazzcash - optional',
                    'payout_account_title' => 'string, optional', 'payout_account_number' => 'string, optional', 'payout_account_provider' => 'string, optional',
                ],
            ])

            @include('admin.system._endpoint', [
                'method' => 'PUT', 'path' => '/me/password', 'auth' => true,
                'description' => 'Change password. Revokes every OTHER token on success (this request\'s token stays valid) - so a stolen session is killed the moment the real owner changes their password.',
                'body' => [
                    'current_password' => 'string, required',
                    'password' => 'string, required, min:8, must match password_confirmation',
                    'password_confirmation' => 'string, required',
                ],
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- RESPONSE FORMAT --}}
        {{-- ============================================================ --}}
        <section id="response-format" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-code text-blue-600 mr-2"></i> Response Format</h2>
            <p class="text-sm text-gray-600 mb-3">Every endpoint documented here returns this envelope:</p>

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

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Paginated list <code>meta</code> block</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>{
  "current_page": 1,
  "last_page": 4,
  "per_page": 20,
  "total": 78
}</code></pre>
            <p class="text-xs text-gray-500 mt-2">
                Paginated endpoints accept <code>?page=2&per_page=20</code>.
                Note: some framework-level errors (invalid/expired token, rate limiting) are rendered directly
                by Laravel before reaching this app's code and use a slightly different shape - see
                <a href="#errors" class="text-blue-600 hover:underline">Error Reference</a>.
            </p>
        </section>

        {{-- ============================================================ --}}
        {{-- DASHBOARD --}}
        {{-- ============================================================ --}}
        <section id="dashboard" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-chart-pie text-blue-600 mr-2"></i> Dashboard</h2>

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/dashboard', 'auth' => true,
                'description' => 'Home-screen stats: totals, this month\'s figures, commission breakdown by type, and a 6-month sales chart - the same numbers as the web agent dashboard.',
                'response' => <<<'JSON'
{
  "success": true,
  "data": {
    "total_sales": 450000.00,
    "total_paid": 320000.00,
    "total_due": 130000.00,
    "total_transactions": 42,
    "recovery_rate": 71.11,
    "current_month": { "total_amount": 55000.00, "commission": 825.00, "paid": 40000.00 },
    "commission_breakdown": { "sale_commission": 6200.00, "new_customer_bonus": 1500.00, "recovery_bonus": 300.00, "target_bonus": 5000.00 },
    "total_commission": 13000.00,
    "total_customers": 18,
    "new_customers_this_month": 2,
    "monthly_sales_chart": { "labels": ["Mar","Apr","May","Jun","Jul","Aug"], "data": [40000,52000,61000,48000,55000,55000] }
  }
}
JSON,
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- CUSTOMERS --}}
        {{-- ============================================================ --}}
        <section id="customers" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-users text-blue-600 mr-2"></i> Customers</h2>
            <p class="text-sm text-gray-600 mb-4">
                Scoped to the agent's own customers only - every endpoint here 404/403s on a customer created
                by someone else. New customers are marked as agent-registered automatically (subject to the
                "Salesman Rule": commission on their sales is held until an admin verifies them).
            </p>

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/customers', 'auth' => true,
                'description' => 'Paginated list. Supports ?search= (matches name/code/phone).',
                'response' => '{"success": true, "data": [ { "id": 12, "code": "IZM-KAR-00012", "name": "...", "customer_group": {"id":1,"name":"Retail","price_field":"sale_price"}, "...": "..." } ], "meta": {"current_page":1,"last_page":1,"per_page":30,"total":18}}',
            ])

            @include('admin.system._endpoint', [
                'method' => 'POST', 'path' => '/customers', 'auth' => true,
                'description' => 'Create a customer under this agent.',
                'body' => [
                    'name' => 'string, required', 'code' => 'string, optional, auto-generated as IZM-{City}-{seq} if omitted',
                    'email' => 'string, optional, unique', 'phone' => 'string, optional', 'mobile' => 'string, optional',
                    'address' => 'string, optional', 'city' => 'string, optional', 'state' => 'string, optional',
                    'country' => 'string, optional', 'postal_code' => 'string, optional', 'cnic' => 'string, optional', 'ntn' => 'string, optional',
                    'opening_balance' => 'numeric, optional (default 0)', 'credit_limit' => 'numeric, optional (default 0)', 'credit_days' => 'integer, optional (default 0)',
                    'notes' => 'string, optional', 'is_active' => 'boolean, optional (default true)',
                    'customer_group_id' => 'integer, optional - see GET /customer-groups',
                ],
            ])

            @include('admin.system._endpoint', ['method' => 'GET', 'path' => '/customers/{id}', 'auth' => true, 'description' => 'Single customer, with their 10 most recent sales.'])
            @include('admin.system._endpoint', ['method' => 'PUT', 'path' => '/customers/{id}', 'auth' => true, 'description' => 'Update a customer. Same body as create.'])
            @include('admin.system._endpoint', ['method' => 'DELETE', 'path' => '/customers/{id}', 'auth' => true, 'description' => 'Delete a customer. Rejected with 422 if they have any sales on record.'])

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/customer-groups', 'auth' => true,
                'description' => 'Reference data for the customer_group_id field - Retail vs Wholesale, and which product price column each one resolves to.',
                'response' => '{"success": true, "data": [ {"id":1,"name":"Retail","price_field":"sale_price","discount_percent":"0.00","is_default":true}, {"id":2,"name":"Wholesale","price_field":"wholesale_price","discount_percent":"0.00","is_default":false} ]}',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- PRODUCTS --}}
        {{-- ============================================================ --}}
        <section id="products" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-box text-blue-600 mr-2"></i> Products</h2>

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/products', 'auth' => true,
                'description' => 'Active, in-stock catalog. Without ?customer_id=, every product includes BOTH prices plus is_retail/is_wholesale so the app can filter/cache and pick a price client-side. With ?customer_id=, the server does that filtering for you (only products valid for that customer\'s group come back, each with a resolved "price" field already picked).',
                'query' => ['search' => 'string, optional - matches name/code', 'customer_id' => 'integer, optional - server-side group filtering'],
                'response' => <<<'JSON'
{
  "success": true,
  "data": [
    {
      "id": 7, "code": "PRD-A1B2", "name": "Cooking Oil 5L", "category": "Groceries", "unit": "piece",
      "sale_price": 1450.00, "wholesale_price": 1300.00, "purchase_price": 1100.00,
      "current_stock": 84.00, "is_retail": true, "is_wholesale": true, "image": null,
      "price": 1300.00, "price_field": "wholesale_price"
    }
  ]
}
JSON,
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- SALES --}}
        {{-- ============================================================ --}}
        <section id="sales" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-shopping-bag text-blue-600 mr-2"></i> Sales</h2>
            <p class="text-sm text-gray-600 mb-4">
                Creating/updating a sale runs through the exact same accounting engine as the web app
                (stock deduction, double-entry ledger posting, commission calculation) - there is no separate,
                simpler "mobile" code path. <code>customer_id</code> must belong to the requesting agent.
            </p>

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/sales', 'auth' => true,
                'description' => 'Paginated list. Filters: ?status=draft|confirmed|partial|paid|cancelled, ?from_date=YYYY-MM-DD, ?to_date=YYYY-MM-DD.',
            ])

            @include('admin.system._endpoint', [
                'method' => 'POST', 'path' => '/sales', 'auth' => true,
                'description' => 'Create a sale. sub_total/total_amount/due_amount are computed server-side from items - do not send them. Stock is deducted and ledger entries posted immediately. If status is "paid", a full payment is recorded automatically in the same request.',
                'body' => [
                    'customer_id' => 'integer, required - must be this agent\'s own customer',
                    'sale_date' => 'date, required', 'payment_term' => 'in: cash,credit - required', 'status' => 'in: draft,confirmed,paid - required',
                    'discount' => 'numeric, optional', 'discount_type' => 'in: fixed,percentage - optional',
                    'tax' => 'numeric, optional', 'shipping_cost' => 'numeric, optional', 'notes' => 'string, optional',
                    'items' => 'array, required, min 1 item',
                    'items.*.product_id' => 'integer, required', 'items.*.quantity' => 'numeric, required, min 0.01',
                    'items.*.unit_price' => 'numeric, required', 'items.*.discount' => 'numeric, optional', 'items.*.tax' => 'numeric, optional',
                ],
                'response' => <<<'JSON'
{
  "success": true,
  "message": "Sale created successfully.",
  "data": {
    "id": 231, "invoice_no": "SA-260804-00231", "customer": {"id":12,"name":"...","code":"...","phone":"..."},
    "status": "confirmed", "status_label": "Confirmed", "status_color": "bg-blue-100 text-blue-800",
    "sub_total": 2900.00, "total_amount": 2900.00, "paid_amount": 0, "due_amount": 2900.00,
    "items": [ {"id":501,"product_id":7,"product_name":"Cooking Oil 5L","quantity":2,"unit_price":1450,"total_price":2900} ],
    "payments": []
  }
}
JSON,
            ])

            @include('admin.system._endpoint', ['method' => 'GET', 'path' => '/sales/{id}', 'auth' => true, 'description' => 'Single sale with items and payment history.'])
            @include('admin.system._endpoint', ['method' => 'PUT', 'path' => '/sales/{id}', 'auth' => true, 'description' => 'Update a sale. Rejected with 422 once status is "paid" - edit is only allowed before that point, same as the web app. Same body as create.'])
            @include('admin.system._endpoint', ['method' => 'DELETE', 'path' => '/sales/{id}', 'auth' => true, 'description' => 'Delete a sale. Reverses stock and ledger entries first. Rejected with 422 if status is "paid".'])

            @include('admin.system._endpoint', [
                'method' => 'POST', 'path' => '/sales/{id}/payments', 'auth' => true,
                'description' => 'Record a payment against a sale (partial or final). For credit sales this is also what triggers commission accrual - commission is earned per payment recovered, not upfront at sale time.',
                'body' => [
                    'amount' => 'numeric, required, min 0.01, max = the sale\'s current due_amount',
                    'payment_date' => 'date, required', 'payment_method' => 'in: cash,bank_transfer,cheque,credit_card - required',
                    'reference_no' => 'string, optional', 'notes' => 'string, optional',
                ],
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- COMMISSIONS --}}
        {{-- ============================================================ --}}
        <section id="commissions" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-coins text-blue-600 mr-2"></i> Commissions</h2>

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/commissions', 'auth' => true,
                'description' => 'Paginated commission ledger.',
                'query' => [
                    'type' => 'sale|new_customer_bonus|recovery_bonus|target_bonus, optional',
                    'status' => 'paid|unpaid, optional', 'from_date' => 'date, optional', 'to_date' => 'date, optional',
                ],
            ])

            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/commissions/summary', 'auth' => true,
                'description' => 'Totals: earned, paid, due, this month, and a breakdown by type.',
                'response' => '{"success": true, "data": {"total_earned": 13000, "total_paid": 8000, "total_due": 5000, "month_earned": 1200, "breakdown": {"sale": 6200, "new_customer_bonus": 1500, "recovery_bonus": 300, "target_bonus": 5000}}}',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- REPORTS --}}
        {{-- ============================================================ --}}
        <section id="reports" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-chart-bar text-blue-600 mr-2"></i> Reports</h2>

            @include('admin.system._endpoint', ['method' => 'GET', 'path' => '/reports/overview', 'auth' => true, 'description' => 'Quick totals: customers, sales, commission (all-time and this month).'])
            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/reports/sales', 'auth' => true,
                'description' => 'Paginated sales list with a totals summary in meta.summary.',
                'query' => ['from_date' => 'date, optional', 'to_date' => 'date, optional', 'status' => 'string, optional'],
            ])
            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/reports/commission', 'auth' => true,
                'description' => 'Paginated commission list with a totals summary in meta.summary.',
                'query' => ['from_date' => 'date, optional', 'to_date' => 'date, optional', 'type' => 'string, optional'],
            ])
            @include('admin.system._endpoint', [
                'method' => 'GET', 'path' => '/reports/target', 'auth' => true,
                'description' => 'Current month\'s target vs. achievement, plus a full 12-month breakdown for the given year (?year=, defaults to current year).',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- ERROR REFERENCE --}}
        {{-- ============================================================ --}}
        <section id="errors" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Error Reference</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Meaning</th><th class="py-2">Typical cause</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="py-2 pr-4"><code class="text-red-600">401</code></td><td class="py-2 pr-4">Unauthenticated</td><td class="py-2">Missing/expired/revoked token, or wrong login credentials.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">403</code></td><td class="py-2 pr-4">Forbidden</td><td class="py-2">Account not active/approved, or accessing another agent's customer/sale.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">404</code></td><td class="py-2 pr-4">Not Found</td><td class="py-2">Record doesn't exist (or belongs to someone else, on route-model-bound endpoints).</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">422</code></td><td class="py-2 pr-4">Validation / business rule failure</td><td class="py-2">Bad input, or a rule like "cannot delete a paid sale". Check <code>errors</code>.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">429</code></td><td class="py-2 pr-4">Too Many Requests</td><td class="py-2">Login attempted more than 6 times in a minute from the same IP.</td></tr>
                        <tr><td class="py-2 pr-4"><code class="text-red-600">500</code></td><td class="py-2 pr-4">Server Error</td><td class="py-2">Unexpected failure - report it, this shouldn't happen.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                401/404/429/500 are rendered by the framework before reaching this app's controllers, so they use
                Laravel's default shape (<code>{"message": "..."}</code>) rather than the <code>{success, message, data}</code>
                envelope. Always check the HTTP status code first, not just the presence of a <code>success</code> key.
            </p>
        </section>

        {{-- ============================================================ --}}
        {{-- FLUTTER INTEGRATION --}}
        {{-- ============================================================ --}}
        <section id="flutter" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-mobile-alt text-blue-600 mr-2"></i> Flutter Integration Guide</h2>

            <p class="text-sm text-gray-600 mb-3">
                <strong>1. Store the token securely.</strong> Use
                <code>flutter_secure_storage</code> (not <code>SharedPreferences</code> - that's unencrypted).
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>2. Send these headers on every request:</strong>
                <code>Content-Type: application/json</code>, <code>Accept: application/json</code>, and once logged in,
                <code>Authorization: Bearer &lt;token&gt;</code>.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>3. On any 401 response,</strong> clear the stored token and send the user back to the login
                screen - it means the token is gone (logged out elsewhere, revoked by a password change, or the
                account was deactivated).
            </p>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Minimal API client (package: <code>http</code> + <code>flutter_secure_storage</code>)</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  static const baseUrl = '{{ url('/api/v1/agent') }}';
  final _storage = const FlutterSecureStorage();

  Future&lt;Map&lt;String, String&gt;&gt; _headers({bool auth = true}) async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (auth) {
      final token = await _storage.read(key: 'api_token');
      if (token != null) headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Future&lt;Map&lt;String, dynamic&gt;&gt; login(String email, String password) async {
    final res = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await _headers(auth: false),
      body: jsonEncode({'email': email, 'password': password, 'device_name': 'flutter-app'}),
    );
    final data = jsonDecode(res.body) as Map&lt;String, dynamic&gt;;
    if (res.statusCode == 200 &amp;&amp; data['success'] == true) {
      await _storage.write(key: 'api_token', value: data['data']['token']);
    }
    return data;
  }

  Future&lt;http.Response&gt; get(String path) async =&gt;
      http.get(Uri.parse('$baseUrl$path'), headers: await _headers());

  Future&lt;http.Response&gt; post(String path, Map&lt;String, dynamic&gt; body) async =&gt;
      http.post(Uri.parse('$baseUrl$path'), headers: await _headers(), body: jsonEncode(body));

  Future&lt;void&gt; logout() async {
    await post('/logout', {});
    await _storage.delete(key: 'api_token');
  }
}</code></pre>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Example: create a sale</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>final res = await api.post('/sales', {
  'customer_id': 12,
  'sale_date': '2026-08-04',
  'payment_term': 'cash',
  'status': 'confirmed',
  'items': [
    {'product_id': 7, 'quantity': 2, 'unit_price': 1450},
  ],
});
final data = jsonDecode(res.body);
if (data['success'] == true) {
  print('Created invoice ${'{'}data['data']['invoice_no']${'}'}');
} else if (res.statusCode == 422) {
  print('Validation errors: ${'{'}data['errors']${'}'}');
}</code></pre>

            <p class="text-sm text-gray-600 mt-4">
                Try any endpoint live (with a real agent account) using the
                <a href="{{ route('admin.system.api.tester') }}" class="text-blue-600 hover:underline font-medium">API Tester</a>
                before wiring it into the app.
            </p>
        </section>
    </div>
    </div>
    {{-- ============================================================ --}}
    {{-- END AGENT API TAB --}}
    {{-- ============================================================ --}}

    {{-- ================================================================ --}}
    {{-- CUSTOMER API TAB --}}
    {{-- ================================================================ --}}
    <div x-show="tab === 'customer'" class="flex flex-col lg:flex-row gap-6">

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
                <a href="#c-agents" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-user-tie w-4 mr-1 text-gray-400"></i> Agents</a>
                <a href="#c-products" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-box w-4 mr-1 text-gray-400"></i> Products</a>
                <a href="#c-orders" class="block px-3 py-1.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600"><i class="fas fa-shopping-cart w-4 mr-1 text-gray-400"></i> Orders</a>
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
                This is the REST API your <strong>seller app</strong> calls to let its sellers - who are
                <code>Customer</code> records in this system - view their profile, browse the catalog, and place
                orders, without ever leaving your app. This is a
                <strong>system-to-system integration</strong>, not a public sign-up flow: there is no password
                screen. Your seller app already owns identity/verification for its users; this API trusts that and
                only asks, once, which sales agent (if any) a seller should be linked to.
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
                <p class="text-sm text-yellow-800"><i class="fas fa-lightbulb mr-1"></i> <strong>Pricing: always wholesale, unconditionally.</strong>
                This entire API is a wholesale-only channel - every product listing, price, and order placed through
                it uses <code>wholesale_price</code> / <code>is_wholesale</code>, full stop. This is <strong>not</strong>
                conditional on the customer's <code>customer_group</code> (that field still exists and still defaults
                to "Wholesale" at <code>/connect</code>, and is still useful for reporting/reconciliation elsewhere in
                WSERP) - even if an admin later changes a customer's group to Retail for some other purpose, their
                Mandi orders still price at wholesale. Price is always resolved server-side from the product record,
                never trusted from a request body.</p>
            </div>
            <div class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800"><i class="fas fa-user-tie mr-1"></i> <strong>Agent linkage is unrelated to pricing.</strong>
                <code>agent_id</code> / <code>direct</code> at <code>/connect</code> only decide who gets
                relationship/commission credit for this customer's orders (<code>created_by_agent_id</code>) - they
                have no effect on price or product availability, which are always wholesale regardless.</p>
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
                <p class="text-sm text-red-800"><i class="fas fa-server mr-1"></i> <strong>Must be called server-to-server, never from the seller's device.</strong>
                If the vendor app calls its own backend directly (as it already does for everything else - see the
                Integration Guide below), that backend's server must be the one calling <code>/connect</code>, holding
                the integration key in its own server-side config. A key shipped inside a compiled mobile app is
                extractable and is not real security - do not call this endpoint from the Flutter/mobile client itself.</p>
            </div>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'POST', 'path' => '/connect', 'auth' => false,
                'description' => 'Creates a new customer, or matches an existing one BY PHONE NUMBER (so it also merges with a customer an agent already registered manually in the web/agent app), then issues a Sanctum token for that one customer. Requires the X-Integration-Key header - see below. Safe to call again for the same phone number later (e.g. app reinstall): it just re-links the profile and issues a fresh token.',
                'body' => [
                    'name' => 'string, required',
                    'business_name' => 'string, optional - shop/business name',
                    'phone' => 'string, required - the matching/identity key. Use a consistent format (e.g. always with or without a country code) since matching is an exact string comparison.',
                    'mobile' => 'string, optional', 'email' => 'string, optional',
                    'address' => 'string, optional', 'city' => 'string, optional', 'gps_location' => 'string, optional',
                    'cnic' => 'string, optional', 'ntn' => 'string, optional',
                    'agent_id' => 'integer, optional - which sales agent gets relationship/commission credit for this customer. Does NOT affect pricing (every seller-app customer defaults to wholesale regardless). Must be an active, approved sales agent.',
                    'direct' => 'boolean, optional - true = no agent ("sold to admin"). Explicitly clears any previously-linked agent on reconnect. Also does not affect pricing.',
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
      "phone": "03001234567", "order_channel": "wholesale",
      "agent": {"id": 5, "name": "John Doe", "phone": "..."},
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
            <p class="text-sm text-gray-600 mb-3">Identical envelope to the Agent API - every endpoint here returns:</p>

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
                Paginated endpoints accept <code>?page=2&per_page=20</code> and return the same
                <code>current_page/last_page/per_page/total</code> block in <code>meta</code> as the Agent API.
            </p>
        </section>

        {{-- ============================================================ --}}
        {{-- PROFILE --}}
        {{-- ============================================================ --}}
        <section id="c-profile" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-id-card text-purple-600 mr-2"></i> Profile</h2>
            <p class="text-sm text-gray-600 mb-4">
                Full self-view: contact details, balance, and pricing channel/linked agent in one response -
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
    "agent": {"id": 5, "name": "John Doe", "phone": "..."},
    "order_channel": "wholesale",
    "customer_group": {"id": 2, "name": "Wholesale", "price_field": "wholesale_price"}
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
                'description' => 'Active categories that have at least one in-stock product available for this customer\'s pricing channel - a category with nothing orderable in it is left out. No images (WSERP\'s Category model doesn\'t have one) - render with a generic icon client-side.',
                'response' => '{"success": true, "data": [ {"id": 3, "name": "Spices"}, {"id": 5, "name": "Groceries"} ]}',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- AGENTS --}}
        {{-- ============================================================ --}}
        <section id="c-agents" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-user-tie text-purple-600 mr-2"></i> Agents</h2>
            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/agents', 'auth' => false,
                'description' => 'Active, approved sales agents (id/name/phone only) - for the one-time picker shown before /connect. Fully public: no token exists yet at that point, and none of this is sensitive. Only agents allowed to work wholesale appear here (channel is "wholesale", "both", or unset) - this whole API is a wholesale-only channel, so a retail-only agent is never offered.',
                'response' => '{"success": true, "data": [ {"id": 5, "name": "John Doe", "phone": "03001112222"} ]}',
            ])
        </section>

        {{-- ============================================================ --}}
        {{-- PRODUCTS --}}
        {{-- ============================================================ --}}
        <section id="c-products" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-box text-purple-600 mr-2"></i> Products</h2>

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/products', 'auth' => true,
                'description' => 'Active, in-stock, wholesale-eligible catalog, already priced at wholesale_price - this API is a wholesale-only channel, unconditionally (see the pricing note above). The "price" field shown is what an order will actually charge - no need to compute it client-side.',
                'query' => ['search' => 'string, optional - matches name/code', 'category_id' => 'integer, optional - from GET /categories'],
                'response' => <<<'JSON'
{
  "success": true,
  "data": [
    {
      "id": 7, "code": "PRD-A1B2", "name": "Cooking Oil 5L", "category_id": 5, "category": "Groceries",
      "description": "Refined cooking oil, 5 litre bottle.", "unit": "piece",
      "sale_price": 1450.00, "wholesale_price": 1300.00, "current_stock": 84.00,
      "is_retail": true, "is_wholesale": true, "image": "uploads/products/abc123.jpg",
      "price": 1300.00, "price_field": "wholesale_price"
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
        {{-- ORDERS --}}
        {{-- ============================================================ --}}
        <section id="c-orders" class="bg-white rounded-xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-3"><i class="fas fa-shopping-cart text-purple-600 mr-2"></i> Orders</h2>
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                <p class="text-sm text-blue-800"><i class="fas fa-info-circle mr-1"></i>
                An order placed here does <strong>not</strong> immediately move stock or post accounting - it's
                created as a <strong>pending order</strong> (internally a normal Sale, <code>status: "draft"</code>,
                <code>source: "customer_app"</code>). It only becomes real - stock deducted, ledger posted - once the
                linked sales agent (or, for a direct/wholesale order, an admin) confirms it via the web/agent app.
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
                'description' => 'Place an order. Send product_id + quantity only - unit_price is always resolved server-side from the product\'s sale_price or wholesale_price depending on this customer\'s pricing channel, never trusted from the request. Rejected with 422 if a product isn\'t available for this channel or requested quantity exceeds current stock.',
                'body' => [
                    'sale_date' => 'date, required',
                    'payment_term' => 'in: cash,credit - required. The customer\'s own choice at checkout (e.g. "Cash on Delivery" vs an account/credit order) - does NOT mean payment has already happened, it only sets the term the confirming agent/admin will collect against.',
                    'notes' => 'string, optional',
                    'items' => 'array, required, min 1 item',
                    'items.*.product_id' => 'integer, required',
                    'items.*.quantity' => 'numeric, required, min 0.01',
                ],
                'response' => <<<'JSON'
{
  "success": true,
  "message": "Order placed! It is pending confirmation from your sales agent.",
  "data": {
    "id": 231, "invoice_no": "SA-260804-00231", "source": "customer_app", "payment_term": "credit",
    "status": "draft", "status_label": "Draft",
    "sub_total": 2600.00, "total_amount": 2600.00, "paid_amount": 0, "due_amount": 2600.00,
    "items": [ {"id":501,"product_id":7,"product_name":"Cooking Oil 5L","quantity":2,"unit_price":1300,"total_price":2600} ]
  }
}
JSON,
            ])

            @include('admin.system._endpoint', ['base' => '/api/v1/customer', 'method' => 'GET', 'path' => '/orders/{id}', 'auth' => true, 'description' => 'Single order with items and payment history.'])

            @include('admin.system._endpoint', [
                'base' => '/api/v1/customer', 'method' => 'POST', 'path' => '/orders/{id}/cancel', 'auth' => true,
                'description' => 'Cancel an order - only while it\'s still pending (status "draft"). Once an agent/admin has confirmed it, cancelling has to go through them since stock/ledger entries now exist.',
            ])

            <div class="mt-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <p class="text-sm text-purple-900 mb-2"><i class="fas fa-route mr-1"></i> <strong>Displaying order status client-side:</strong>
                WSERP only tracks payment/posting status (no delivery/logistics tracking exists). Map it for display as:</p>
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-purple-700"><th class="py-1 pr-3">WSERP <code>status</code></th><th class="py-1">Show as</th></tr></thead>
                    <tbody class="divide-y divide-purple-100">
                        <tr><td class="py-1 pr-3 font-mono">draft</td><td class="py-1">Pending</td></tr>
                        <tr><td class="py-1 pr-3 font-mono">confirmed / partial / paid</td><td class="py-1">Delivered (agent/admin confirming an order is treated as fulfilling it - there's no separate "out for delivery" step today)</td></tr>
                        <tr><td class="py-1 pr-3 font-mono">cancelled</td><td class="py-1">Cancelled / Rejected</td></tr>
                    </tbody>
                </table>
                <p class="text-xs text-purple-700 mt-2">This is a client-side display convention only, not a WSERP field. If real delivery/logistics tracking (a genuine "out for delivery" step, driver assignment, etc.) becomes a real need later, that would be a separate addition to WSERP's Sale model - not simulated by this mapping.</p>
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
                Written for the "Mandi" feature in the vendor app: today its <code>MandiController</code> /
                <code>MandiCartController</code> / <code>MandiOrdersController</code> hold dummy in-memory data and
                make no network calls at all - this is the contract to replace that with real calls to this API.
            </p>

            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-sitemap mr-1"></i> Split by who calls what</p>
                <p class="text-sm text-gray-600 mb-2">
                    Only <code>/connect</code> needs the integration key, and it's the only call that must not
                    originate from the phone. Everything after that uses the ordinary per-customer Bearer token, the
                    same trust model the app already uses for its own backend - so it's safe to call directly from
                    the device.
                </p>
                <table class="w-full text-xs mt-2">
                    <thead><tr class="text-left text-gray-500"><th class="py-1 pr-4">Call</th><th class="py-1">Who makes it</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr><td class="py-1.5 pr-4 font-mono">POST /connect</td><td class="py-1.5">Your <strong>izmafood.com backend</strong> (new endpoint there, e.g. <code>POST /api/mandi/connect</code>) - holds the integration key server-side, calls WSERP, returns the resulting <code>token</code> back to the app in its own response.</td></tr>
                        <tr><td class="py-1.5 pr-4 font-mono">Everything else<br>(/me, /products, /orders)</td><td class="py-1.5">The <strong>Flutter app directly</strong>, using the token from step above - a second API client alongside the existing <code>APIHelper</code>, pointed at WSERP's base URL instead of <code>kBaseApiUrl</code>.</td></tr>
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-gray-600 mb-3">
                <strong>1. Trigger connect once, lazily.</strong> The "Mandi" button in <code>home_page.dart</code>
                currently does a bare <code>Get.to(() =&gt; MandiHomePage())</code>. Change it to first check whether a
                WSERP token is already stored (a new local-storage key, alongside <code>sKeyToken</code>); if not,
                show a one-time screen asking the vendor to pick their sales agent or "Direct / Admin", then call
                your backend's proxy endpoint with that choice plus the profile data already sitting in
                <code>AuthController.loginModel.value.data.user</code> (<code>name</code>, <code>mobile</code>,
                <code>shop.shopName</code>, <code>shop.faddress</code>, <code>shop.lat</code>/<code>lng</code>,
                <code>shop.cnic</code>, <code>shop.ntn</code>) - nothing needs to be re-typed. Store the returned
                WSERP token, then proceed into <code>MandiHomePage</code>.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>2. Agent/Direct is asked once, not per order.</strong> It's stored on the WSERP customer
                record and can be changed later by an admin - never re-collect it on subsequent orders.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>3. Reconnecting is safe.</strong> If the stored WSERP token is ever missing or a call comes
                back <code>401</code>, just repeat step 1 - <code>/connect</code> matches the existing customer by
                phone rather than creating a duplicate.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>4. Checkout maps directly onto the existing cart UI.</strong> <code>MandiCartController</code>'s
                "Cash on Delivery" / "Wallet" payment methods become <code>payment_term: "cash"</code> for either (see
                the note on that field above - neither means payment already happened), <code>deliveryAddressController</code>'s
                text can ride along in <code>notes</code> since there's no separate delivery-address field on the
                order today, and <code>MandiCartItem</code> → <code>items[]</code> is a direct mapping.
            </p>
            <p class="text-sm text-gray-600 mb-3">
                <strong>5. Not carried over from the dummy data:</strong> <code>MandiProduct.minOrderQty</code> /
                <code>stepQty</code> and <code>MandiCategory</code> have no equivalent in WSERP's product catalog
                today (no min-order-quantity or category-grouping fields exist on <code>Product</code>). Either drop
                bulk-increment enforcement for now, or treat it as a follow-up WSERP change if it's actually needed -
                don't invent client-side values that don't reflect real catalog data.
            </p>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Example: your backend's proxy endpoint (holds the integration key)</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>// izmafood.com backend, e.g. POST /api/mandi/connect - called by the Flutter app
// with ITS OWN existing Bearer token (so this endpoint knows which vendor is asking)
$vendor = auth()->user(); // your existing vendor auth, unrelated to WSERP

$wserpRes = Http::withHeaders([
    'X-Integration-Key' => config('services.wserp.integration_key'), // your server-side secret
])->post('{{ url('/api/v1/customer/connect') }}', [
    'name' => $vendor->name,
    'business_name' => $vendor->shop->shop_name,
    'phone' => $vendor->mobile,
    'address' => $vendor->shop->faddress,
    'gps_location' => "{$vendor->shop->lat},{$vendor->shop->lng}",
    'cnic' => $vendor->shop->cnic,
    'ntn' => $vendor->shop->ntn,
    'agent_id' => $request->input('agent_id'), // from the one-time picker screen
    'direct' => $request->boolean('direct'),
]);

return response()->json(['wserp_token' => $wserpRes->json('data.token')]);</code></pre>

            <p class="text-xs font-semibold text-gray-500 uppercase mt-4 mb-1">Example: Flutter app, everything after connect (direct to WSERP)</p>
            <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto"><code>final orderRes = await Dio(BaseOptions(baseUrl: wserpBaseUrl)).post(
  '/orders',
  data: {
    'sale_date': DateTime.now().toIso8601String().split('T').first,
    'payment_term': paymentMethod.value == 'Wallet' ? 'credit' : 'cash',
    'notes': deliveryAddressController.text,
    'items': cartItems.map((c) => {
      'product_id': c.product.id,
      'quantity': c.quantity,
    }).toList(),
  },
  options: Options(headers: {
    'Accept': 'application/json',
    'Authorization': 'Bearer $wserpToken',
  }),
);</code></pre>

            <p class="text-sm text-gray-600 mt-4">
                <i class="fas fa-info-circle mr-1"></i> The <a href="{{ route('admin.system.api.tester') }}" class="text-purple-600 hover:underline font-medium">API Tester</a>
                page currently only drives the Agent API's email/password login flow - it doesn't yet know how to call
                <code>/connect</code> with an integration key. Use curl or Postman against the base URL above until the
                tester is extended to cover this tab too.
            </p>
        </section>
    </div>
    </div>
    {{-- ============================================================ --}}
    {{-- END CUSTOMER API TAB --}}
    {{-- ============================================================ --}}

</div>
@endsection

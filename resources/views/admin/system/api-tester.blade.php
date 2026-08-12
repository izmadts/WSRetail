@extends('layouts.admin')

@section('title', 'API Testing')
@section('page-title', 'Sale Agent API Tester')

@section('content')
<div x-data="apiTester()" x-init="init()" class="space-y-4">

    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
        <div class="text-sm text-yellow-800">
            <strong>This tool makes real requests against live data</strong> - a POST/PUT/DELETE sent here has
            the exact same effect as the same request from the Flutter app (creates real sales, deducts real
            stock, posts real ledger entries). Use a test agent account, not production data, unless you mean it.
            See the <a href="{{ route('admin.system.api.docs') }}" class="underline font-medium">API Documentation</a> for request/response shapes.
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        <!-- Endpoint presets -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-card p-4 lg:sticky lg:top-4 max-h-[80vh] overflow-y-auto">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Endpoints</p>
                <template x-for="group in presetGroups" :key="group.label">
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-gray-500 mb-1" x-text="group.label"></p>
                        <div class="space-y-0.5">
                            <template x-for="p in group.items" :key="p.method + p.path">
                                <button type="button" @click="selectPreset(p)"
                                        class="w-full text-left px-2 py-1.5 rounded-lg hover:bg-blue-50 flex items-center gap-2 text-xs">
                                    <span class="font-mono font-bold w-10 flex-shrink-0"
                                          :class="{'text-blue-600': p.method === 'GET', 'text-green-600': p.method === 'POST', 'text-yellow-600': p.method === 'PUT', 'text-red-600': p.method === 'DELETE'}"
                                          x-text="p.method"></span>
                                    <span class="text-gray-700 truncate" x-text="p.path"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Request builder + response -->
        <div class="lg:col-span-3 space-y-4">

            <!-- Login Helper -->
            <div class="bg-white rounded-xl shadow-card p-4">
                <p class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-key text-purple-600 mr-1"></i> Login Helper</p>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-end">
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Agent Email</label>
                        <input type="email" x-model="loginEmail" placeholder="agent@example.com" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg">
                    </div>
                    <div class="sm:col-span-1" x-data="{ showPassword: false }">
                        <label class="block text-xs text-gray-500 mb-1">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="loginPassword" class="w-full px-2 py-1.5 pr-8 text-sm border border-gray-300 rounded-lg">
                            <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-500 hover:text-purple-600">
                                <i class="fas text-xs" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" @click="doLogin()" :disabled="loginLoading"
                            class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:opacity-50">
                        <span x-show="!loginLoading"><i class="fas fa-sign-in-alt mr-1"></i> Login &amp; Fill Token</span>
                        <span x-show="loginLoading"><i class="fas fa-spinner fa-spin mr-1"></i> Logging in...</span>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2" x-show="loginNote" x-text="loginNote"></p>
            </div>

            <!-- Request Builder -->
            <div class="bg-white rounded-xl shadow-card p-4">
                <p class="text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-paper-plane text-blue-600 mr-1"></i> Request</p>

                <div class="flex flex-wrap gap-2 mb-2">
                    <select x-model="method" class="px-2 py-1.5 text-sm border border-gray-300 rounded-lg font-mono font-bold">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                    <input type="text" x-model="path" placeholder="/dashboard"
                           class="flex-1 min-w-[200px] px-2 py-1.5 text-sm border border-gray-300 rounded-lg font-mono">
                    <button type="button" @click="send()" :disabled="loading"
                            class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                        <span x-show="!loading"><i class="fas fa-play mr-1"></i> Send</span>
                        <span x-show="loading"><i class="fas fa-spinner fa-spin mr-1"></i> Sending...</span>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mb-3 font-mono" x-text="baseUrl + path"></p>

                <label class="block text-xs text-gray-500 mb-1">Bearer Token</label>
                <input type="text" x-model="token" placeholder="Paste a token, or use the Login Helper above"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg font-mono mb-3">

                <div x-show="['POST', 'PUT', 'PATCH'].includes(method)">
                    <label class="block text-xs text-gray-500 mb-1">JSON Body</label>
                    <textarea x-model="body" rows="8" spellcheck="false"
                              class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-lg font-mono"></textarea>
                </div>
            </div>

            <!-- Response -->
            <div class="bg-white rounded-xl shadow-card p-4" x-show="response">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-gray-700"><i class="fas fa-reply text-green-600 mr-1"></i> Response</p>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2 py-0.5 rounded-full font-bold"
                              :class="response && response.ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                              x-text="response ? response.status + ' ' + response.statusText : ''"></span>
                        <span class="text-gray-400" x-text="response ? response.time + 'ms' : ''"></span>
                    </div>
                </div>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-3 text-xs overflow-x-auto max-h-[500px] overflow-y-auto" x-text="response ? response.pretty : ''"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function apiTester() {
    return {
        baseUrl: '{{ url('/api/v1/agent') }}',
        token: '',
        method: 'GET',
        path: '/dashboard',
        body: '',
        loading: false,
        response: null,
        loginEmail: '',
        loginPassword: '',
        loginLoading: false,
        loginNote: '',

        presetGroups: [
            { label: 'Auth', items: [
                { method: 'POST', path: '/login', body: { email: 'agent@example.com', password: 'secret', device_name: 'api-tester' } },
                { method: 'POST', path: '/logout', body: null },
                { method: 'GET', path: '/me', body: null },
                { method: 'PUT', path: '/me', body: { name: 'Agent Name', email: 'agent@example.com', city: 'Karachi' } },
                { method: 'PUT', path: '/me/password', body: { current_password: 'old-pass', password: 'new-pass-123', password_confirmation: 'new-pass-123' } },
            ]},
            { label: 'Dashboard', items: [
                { method: 'GET', path: '/dashboard', body: null },
            ]},
            { label: 'Customers', items: [
                { method: 'GET', path: '/customers', body: null },
                { method: 'POST', path: '/customers', body: { name: 'Test Customer', phone: '03001234567', customer_group_id: 1, is_active: true } },
                { method: 'GET', path: '/customers/1', body: null },
                { method: 'PUT', path: '/customers/1', body: { name: 'Updated Name', is_active: true } },
                { method: 'DELETE', path: '/customers/1', body: null },
                { method: 'GET', path: '/customer-groups', body: null },
            ]},
            { label: 'Products', items: [
                { method: 'GET', path: '/products', body: null },
                { method: 'GET', path: '/products?customer_id=1', body: null },
            ]},
            { label: 'Sales', items: [
                { method: 'GET', path: '/sales', body: null },
                { method: 'POST', path: '/sales', body: { customer_id: 1, sale_date: new Date().toISOString().slice(0, 10), payment_term: 'cash', status: 'confirmed', items: [{ product_id: 1, quantity: 1, unit_price: 100 }] } },
                { method: 'GET', path: '/sales/1', body: null },
                { method: 'PUT', path: '/sales/1', body: { customer_id: 1, sale_date: new Date().toISOString().slice(0, 10), payment_term: 'cash', status: 'draft', items: [{ product_id: 1, quantity: 1, unit_price: 100 }] } },
                { method: 'DELETE', path: '/sales/1', body: null },
                { method: 'POST', path: '/sales/1/payments', body: { amount: 100, payment_date: new Date().toISOString().slice(0, 10), payment_method: 'cash' } },
            ]},
            { label: 'Commissions', items: [
                { method: 'GET', path: '/commissions', body: null },
                { method: 'GET', path: '/commissions/summary', body: null },
            ]},
            { label: 'Reports', items: [
                { method: 'GET', path: '/reports/overview', body: null },
                { method: 'GET', path: '/reports/sales', body: null },
                { method: 'GET', path: '/reports/commission', body: null },
                { method: 'GET', path: '/reports/target', body: null },
            ]},
        ],

        init() {
            const saved = localStorage.getItem('api_tester_token');
            if (saved) this.token = saved;
            this.$watch('token', (value) => localStorage.setItem('api_tester_token', value || ''));
        },

        selectPreset(preset) {
            this.method = preset.method;
            this.path = preset.path;
            this.body = preset.body ? JSON.stringify(preset.body, null, 2) : '';
            this.response = null;
        },

        async send() {
            this.loading = true;
            this.response = null;
            const start = performance.now();

            try {
                const headers = { 'Accept': 'application/json' };
                if (this.token) headers['Authorization'] = 'Bearer ' + this.token;

                const options = { method: this.method, headers };
                if (['POST', 'PUT', 'PATCH'].includes(this.method) && this.body.trim()) {
                    headers['Content-Type'] = 'application/json';
                    options.body = this.body;
                }

                const res = await fetch(this.baseUrl + this.path, options);
                const time = Math.round(performance.now() - start);
                const text = await res.text();

                let parsed;
                try { parsed = JSON.parse(text); } catch (e) { parsed = text; }

                this.response = {
                    status: res.status,
                    statusText: res.statusText,
                    ok: res.ok,
                    time: time,
                    pretty: typeof parsed === 'string' ? parsed : JSON.stringify(parsed, null, 2),
                };
            } catch (e) {
                this.response = { status: 0, statusText: 'Network Error', ok: false, time: 0, pretty: e.message };
            } finally {
                this.loading = false;
            }
        },

        async doLogin() {
            if (!this.loginEmail || !this.loginPassword) {
                this.loginNote = 'Enter an email and password first.';
                return;
            }
            this.loginLoading = true;
            this.loginNote = '';
            try {
                const res = await fetch(this.baseUrl + '/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email: this.loginEmail, password: this.loginPassword, device_name: 'api-tester' }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.token = data.data.token;
                    this.loginNote = 'Logged in as ' + data.data.agent.name + ' - token filled in below.';
                } else {
                    this.loginNote = 'Login failed: ' + (data.message || res.statusText);
                }
            } catch (e) {
                this.loginNote = 'Network error: ' + e.message;
            } finally {
                this.loginLoading = false;
            }
        },
    };
}
</script>
@endsection

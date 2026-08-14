@extends('layouts.admin')

@section('title', 'Integrations')
@section('page-title', 'Integrations')

@section('content')
<div class="max-w-4xl space-y-4">

    @if (session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if ($redirectCount > 0)
    <div class="bg-white rounded-xl shadow-card px-6 py-4 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-gray-900"><i class="fas fa-route text-amber-500 mr-1"></i> {{ $redirectCount }} SEO redirect{{ $redirectCount === 1 ? '' : 's' }} captured</p>
            <p class="text-xs text-gray-500">Old store product URLs mapped to their new page here - export these for your web server so search rankings aren't lost when the old site goes away.</p>
        </div>
        <a href="{{ route('admin.integrations.redirects') }}" class="shrink-0 px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">View Redirects</a>
    </div>
    @endif

    <!-- WooCommerce -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center"><i class="fab fa-wordpress text-purple-600 text-xl"></i></div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900">WooCommerce</h4>
                    <p class="text-xs text-gray-500">Import products, customers, and orders from a WooCommerce store</p>
                </div>
            </div>
            @if ($woocommerce->isConnected())
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i> Connected</span>
            @else
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Not connected</span>
            @endif
        </div>

        <div class="p-6">
            @if ($woocommerce->isConnected())
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 mb-4 text-sm">
                    <span class="text-gray-600"><i class="fas fa-link mr-1 text-gray-400"></i> {{ $woocommerce->credentials['site_url'] ?? '' }}</span>
                    <form method="POST" action="{{ route('admin.integrations.woocommerce.disconnect') }}" onsubmit="return confirm('Disconnect WooCommerce? You can reconnect any time.')">
                        @csrf
                        <button class="text-red-600 hover:text-red-800 text-xs font-medium">Disconnect</button>
                    </form>
                </div>

                <p class="text-sm font-semibold text-gray-800 mb-2">Import</p>
                <p class="text-xs text-gray-500 mb-3">
                    Each import fetches from WooCommerce and shows you a review screen first - nothing is added or
                    changed in WSRetail until you confirm. Recommended order: Settings, then Products, then Customers,
                    then Orders (orders need matching products/customers to import cleanly). Variable products import
                    with their full variant/attribute/image detail - see the review screen.
                </p>
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach (['settings' => ['fa-sliders-h', 'Import Settings'], 'products' => ['fa-box', 'Import Products'], 'customers' => ['fa-users', 'Import Customers'], 'orders' => ['fa-shopping-cart', 'Import Orders']] as $type => $meta)
                    <form method="POST" action="{{ route('admin.integrations.woocommerce.import', $type) }}">
                        @csrf
                        <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">
                            <i class="fas {{ $meta[0] }} mr-1"></i> {{ $meta[1] }}
                        </button>
                    </form>
                    @endforeach
                </div>

                @if ($recentBatches->isNotEmpty())
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Recent Imports</p>
                    <div class="space-y-1.5">
                        @foreach ($recentBatches as $batch)
                        <div class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                            <span class="text-gray-700 capitalize">{{ $batch->type }} - {{ $batch->created_at->diffForHumans() }}</span>
                            @if ($batch->status === 'staged')
                                <a href="{{ route('admin.integrations.imports.review', $batch) }}" class="text-blue-600 hover:underline text-xs font-medium">Review ({{ $batch->fetched_count }} fetched)</a>
                            @elseif ($batch->status === 'committed')
                                <span class="text-xs text-gray-500">{{ $batch->created_count }} created, {{ $batch->updated_count }} updated, {{ $batch->skipped_count }} skipped</span>
                            @else
                                <span class="text-xs text-gray-400">Cancelled</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
                <form method="POST" action="{{ route('admin.integrations.woocommerce.connect') }}" class="space-y-3 mb-5">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Site URL *</label>
                        <input type="url" name="site_url" required placeholder="https://yourstore.com" value="{{ old('site_url') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Consumer Key *</label>
                            <input type="text" name="consumer_key" required minlength="10" placeholder="ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" autocomplete="off"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Consumer Secret *</label>
                            <input type="password" name="consumer_secret" required minlength="10" placeholder="cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" autocomplete="off"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
                        </div>
                    </div>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
                        <i class="fas fa-plug mr-1"></i> Connect & Test
                    </button>
                </form>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-900 space-y-1">
                    <p class="font-semibold">How to get your WooCommerce API keys:</p>
                    <ol class="list-decimal list-inside space-y-0.5">
                        <li>In WordPress admin, go to <strong>WooCommerce &gt; Settings &gt; Advanced &gt; REST API</strong>.</li>
                        <li>Click <strong>Add key</strong>, give it a description, set permissions to <strong>Read</strong>, and generate.</li>
                        <li>Copy the Consumer Key and Consumer Secret shown (WooCommerce only shows the secret once) and paste them above.</li>
                        <li>Your Site URL must start with <code>https://</code> - WooCommerce rejects these credentials over plain HTTP.</li>
                    </ol>
                </div>
            @endif
        </div>
    </div>

    <!-- Coming soon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ([
            ['icon' => 'fa-shopify', 'brand' => true, 'name' => 'Shopify', 'desc' => 'Import products, customers, and orders from Shopify'],
            ['icon' => 'fa-cart-shopping', 'brand' => false, 'name' => 'Magento', 'desc' => 'Import products, customers, and orders from Magento'],
            ['icon' => 'fa-whatsapp', 'brand' => true, 'name' => 'WhatsApp', 'desc' => 'Order/receipt notifications via WhatsApp'],
            ['icon' => 'fa-sms', 'brand' => false, 'name' => 'SMS', 'desc' => 'Order/payment reminders via SMS'],
        ] as $card)
        <div class="bg-white rounded-xl shadow-card p-5 opacity-60">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                    <i class="{{ $card['brand'] ? 'fab' : 'fas' }} {{ $card['icon'] }} text-gray-500"></i>
                </div>
                <h4 class="text-sm font-semibold text-gray-800">{{ $card['name'] }}</h4>
                <span class="ml-auto px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500">Coming soon</span>
            </div>
            <p class="text-xs text-gray-500">{{ $card['desc'] }}</p>
        </div>
        @endforeach
    </div>

</div>
@endsection

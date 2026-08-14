@extends('layouts.admin')

@section('title', 'SEO Redirects')
@section('page-title', 'SEO Redirects')

@section('content')
<div class="max-w-5xl space-y-4">

    @if (session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.integrations.index') }}" class="text-gray-500 hover:text-gray-800"><i class="fas fa-arrow-left mr-1"></i> Integrations</a>
    </div>

    <div class="bg-white rounded-xl shadow-card p-6 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-gray-900 mb-1">Storefront URL</p>
            <p class="text-xs text-gray-500">
                Used to build the full redirect destination in the exported rule files below - not required for the
                built-in <code>/api/v1/customer/redirect</code> lookup, which returns a relative path either way.
            </p>
            <p class="text-sm font-mono text-gray-700 mt-2">{{ $storefrontUrl ?: 'Not set' }}</p>
        </div>
        <a href="{{ route('admin.settings.ecommerce.store') }}" class="shrink-0 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-pen mr-1"></i> Edit in Ecommerce Settings
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-base font-semibold text-gray-900">Captured Redirects</h4>
                <p class="text-xs text-gray-500">Every old-store URL WSRetail has mapped to a new product page, from every product import so far.</p>
            </div>
            @if ($redirects->total() > 0)
            <div class="flex gap-2">
                <a href="{{ route('admin.integrations.redirects.export', 'htaccess') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-download mr-1"></i> .htaccess
                </a>
                <a href="{{ route('admin.integrations.redirects.export', 'nginx') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-download mr-1"></i> nginx.conf
                </a>
            </div>
            @endif
        </div>

        @if ($redirects->isEmpty())
            <div class="p-10 text-center text-sm text-gray-400">
                No redirects captured yet - they're added automatically the next time you import products from a
                connected CMS.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Old URL</th>
                            <th class="px-6 py-3 text-left">Product</th>
                            <th class="px-6 py-3 text-left">New URL</th>
                            <th class="px-6 py-3 text-left">Captured</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($redirects as $redirect)
                        <tr>
                            <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $redirect->old_path }}</td>
                            <td class="px-6 py-3 text-gray-800">{{ $redirect->product->name ?? '—' }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $storefrontUrl }}{{ $redirect->new_path }}</td>
                            <td class="px-6 py-3 text-xs text-gray-400">{{ $redirect->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">{{ $redirects->links() }}</div>
        @endif
    </div>

</div>
@endsection

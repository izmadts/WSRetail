@extends('layouts.admin')

@section('title', 'License')
@section('page-title', 'License')

@section('content')
<div class="max-w-2xl space-y-4">

    @if (session('success'))
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($locked)
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            <p class="font-semibold mb-1"><i class="fas fa-lock mr-1"></i> This installation is locked.</p>
            <p>{{ $lockReason }}</p>
            @unless ($canManage)
                <p class="mt-1">Contact your system administrator to resolve this.</p>
            @endunless
        </div>
    @else
        <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            <i class="fas fa-check-circle mr-1"></i> This installation is licensed and active.
        </div>
    @endif

    @if ($license->isActivated())
        <div class="bg-white rounded-xl shadow-card p-4 space-y-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">License Key</p>
                    <p class="font-mono">{{ $license->license_key }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Status</p>
                    @php
                        $badge = match($license->remote_status) {
                            'active' => 'bg-green-100 text-green-800',
                            'suspended' => 'bg-yellow-100 text-yellow-800',
                            'revoked', 'expired' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($license->remote_status ?? 'unknown') }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Domain</p>
                    <p>{{ $license->domain }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Expires</p>
                    <p>{{ $license->expires_at?->format('Y-m-d') ?? 'Never (perpetual)' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Last Checked In</p>
                    <p>{{ $license->last_validated_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Activated</p>
                    <p>{{ $license->activated_at?->format('Y-m-d') ?? '-' }}</p>
                </div>
            </div>
            @if ($license->last_error)
                <p class="text-xs text-red-600">Last check error: {{ $license->last_error }}</p>
            @endif
        </div>

        @if ($canManage)
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.license.recheck') }}">
                @csrf
                <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">
                    <i class="fas fa-sync mr-1"></i> Re-check Now
                </button>
            </form>
            <form method="POST" action="{{ route('admin.license.deactivate') }}" onsubmit="return confirm('Deactivate this installation? You will need to re-activate with a license key before continuing.')">
                @csrf
                <button class="px-4 py-2 border border-red-300 text-red-700 rounded-lg text-sm font-medium hover:bg-red-50">
                    Deactivate / Change Key
                </button>
            </form>
        </div>
        @endif
    @elseif ($canManage)
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-600 mb-3">Enter your license key to activate this installation.</p>
            <form method="POST" action="{{ route('admin.license.activate') }}" class="flex gap-2">
                @csrf
                <input type="text" name="license_key" required placeholder="WSR-XXXXX-XXXXX-XXXXX-XXXXX"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono">
                <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">
                    Activate
                </button>
            </form>
        </div>

        @include('admin.license._purchase-form')
    @endif

</div>
@endsection

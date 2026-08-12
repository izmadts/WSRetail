@extends('layouts.admin')

@section('title', 'Account Ledger - ' . $account->name)
@section('page-title', 'Account Ledger')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $account->code }} - {{ $account->name }}</h3>
            <p class="text-sm text-gray-500">{{ $account->type }} &middot; Normal balance: {{ $account->normal_balance }}</p>
        </div>
        <a href="{{ route('admin.accounts.show', $account) }}" class="text-blue-600 hover:underline text-sm">
            <i class="fas fa-cog mr-1"></i> Manage this account
        </a>
    </div>

    @include('admin.reports.partials.ledger-filter', [
        'action' => route('admin.reports.account-ledger', $account),
        'pdfUrl' => route('admin.reports.account-ledger-pdf', [$account] + request()->only(['from_date', 'to_date'])),
    ])

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            @include('admin.reports.partials.ledger-table')
        </div>
    </div>
</div>
@endsection

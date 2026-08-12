@extends('layouts.admin')

@section('title', 'Supplier Ledger - ' . $supplier->name)
@section('page-title', 'Supplier Ledger')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $supplier->name }}</h3>
            <p class="text-sm text-gray-500">{{ $supplier->code }} @if($supplier->phone) &middot; {{ $supplier->phone }} @endif @if($supplier->city) &middot; {{ $supplier->city }} @endif</p>
        </div>
        <a href="{{ route('admin.reports.supplier-detail', $supplier) }}" class="text-blue-600 hover:underline text-sm">
            <i class="fas fa-list mr-1"></i> View Purchase/Payment History instead
        </a>
    </div>

    <p class="text-xs text-gray-500 -mt-2">Only credit-term purchases affect this balance - cash purchases settle instantly and are excluded, matching the balance shown on this supplier's profile.</p>

    @include('admin.reports.partials.ledger-filter', [
        'action' => route('admin.reports.supplier-ledger', $supplier),
        'pdfUrl' => route('admin.reports.supplier-ledger-pdf', [$supplier] + request()->only(['from_date', 'to_date'])),
    ])

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            @include('admin.reports.partials.ledger-table')
        </div>
    </div>
</div>
@endsection

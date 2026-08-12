@extends('layouts.admin')

@section('title', 'Customer Ledger - ' . $customer->name)
@section('page-title', 'Customer Ledger')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $customer->name }}</h3>
            <p class="text-sm text-gray-500">{{ $customer->code }} @if($customer->phone) &middot; {{ $customer->phone }} @endif @if($customer->city) &middot; {{ $customer->city }} @endif</p>
        </div>
        <a href="{{ route('admin.reports.customer-detail', $customer) }}" class="text-blue-600 hover:underline text-sm">
            <i class="fas fa-list mr-1"></i> View Sales/Payment History instead
        </a>
    </div>

    @include('admin.reports.partials.ledger-filter', [
        'action' => route('admin.reports.customer-ledger', $customer),
        'pdfUrl' => route('admin.reports.customer-ledger-pdf', [$customer] + request()->only(['from_date', 'to_date'])),
    ])

    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            @include('admin.reports.partials.ledger-table')
        </div>
    </div>
</div>
@endsection

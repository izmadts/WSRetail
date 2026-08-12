@extends('layouts.admin')

@section('title', 'Edit Customer Group')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Customer Group</h4>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.settings.customer-groups.update', $customerGroup) }}" method="POST">
            @include('admin.settings.customer-groups._form')
        </form>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Add Location')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-plus-circle text-blue-600 mr-2"></i> Add Location</h4>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.settings.locations.store') }}" method="POST">
            @include('admin.settings.locations._form')
        </form>
    </div>
</div>
@endsection

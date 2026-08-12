@extends('layouts.admin')

@section('title', 'Pending Agents')
@section('page-title', 'Pending Agent Approvals')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-clock text-gray-400 mr-2"></i> Pending Approvals
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $agents->count() }} pending</span>
        </div>
        <a href="{{ route('admin.agents.index') }}"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-1"></i> All Agents
        </a>
    </div>

    <div class="p-6">
        @if($agents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($agents as $agent)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ $agent->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $agent->email }}</p>
                        <p class="text-sm text-gray-600">Phone: {{ $agent->phone ?? '-' }}</p>
                        <p class="text-sm text-gray-600">CNIC: {{ $agent->cnic ?? '-' }}</p>
                        <p class="text-sm text-gray-600">City: {{ $agent->city ?? '-' }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $agent->created_at->format('d-m-Y H:i') }}
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('admin.agents.view', $agent) }}"
                        class="flex-1 px-3 py-1.5 bg-blue-600 text-white text-sm text-center rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-eye mr-1"></i> View 
                    </a>
                    <a href="{{ route('admin.agents.approve', $agent) }}"
                        class="flex-1 px-3 py-1.5 bg-green-600 text-white text-sm text-center rounded-lg hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-check mr-1"></i> Approve
                    </a>
                    <a href="{{ route('admin.agents.reject', $agent) }}"
                        class="flex-1 px-3 py-1.5 bg-red-600 text-white text-sm text-center rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i> Reject
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
            <p class="text-gray-500">No pending approvals</p>
        </div>
        @endif
    </div>
</div>
@endsection
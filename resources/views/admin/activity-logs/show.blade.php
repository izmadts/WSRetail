@extends('layouts.admin')

@section('title', 'Activity Log Details')
@section('page-title', 'Activity Log Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user text-white text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">{{ $activityLog->user_name ?? 'System' }}</h3>
                <p class="text-sm text-gray-500">{{ $activityLog->user_email ?? '' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $activityLog->user_role ?? 'N/A' }}</p>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Module</span>
                        <span class="font-medium">{{ $activityLog->module_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Action</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activityLog->action_color }}">
                            {{ $activityLog->action_label }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date</span>
                        <span class="font-medium">{{ $activityLog->formatted_date }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">IP Address</span>
                        <span class="font-medium">{{ $activityLog->ip_address ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Method</span>
                        <span class="font-medium">{{ $activityLog->method ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">URL</span>
                        <span class="font-medium text-xs truncate">{{ $activityLog->url ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t">
                <a href="{{ route('admin.activity-logs.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-info-circle text-gray-400 mr-2"></i> Details
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Description</p>
                        <p class="text-gray-900 mt-1">{{ $activityLog->description }}</p>
                    </div>

                    @if($activityLog->old_data)
                    <div>
                        <p class="text-sm font-medium text-gray-700">Old Data</p>
                        <div class="bg-gray-50 rounded-lg p-4 mt-1 overflow-x-auto">
                            <pre class="text-xs text-gray-700">{{ json_encode($activityLog->old_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif

                    @if($activityLog->new_data)
                    <div>
                        <p class="text-sm font-medium text-gray-700">New Data</p>
                        <div class="bg-gray-50 rounded-lg p-4 mt-1 overflow-x-auto">
                            <pre class="text-xs text-gray-700">{{ json_encode($activityLog->new_data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif

                    <div>
                        <p class="text-sm font-medium text-gray-700">User Agent</p>
                        <p class="text-xs text-gray-600 mt-1 break-all">{{ $activityLog->user_agent ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
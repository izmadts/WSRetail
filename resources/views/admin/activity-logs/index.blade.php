@extends('layouts.admin')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="space-y-6">

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Activities</p>
                    <p class="text-2xl font-bold">{{ $totalLogs }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Today</p>
                    <p class="text-2xl font-bold text-green-600">{{ $todayLogs }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">This Month</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $thisMonthLogs }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Users</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $uniqueUsers }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                <select name="user_id" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                <select name="module" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All Modules</option>
                    @foreach($modules as $module)
                    <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                        {{ ucfirst($module) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                <select name="action" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All Actions</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ ucfirst($action) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
            <div class="pt-6">
                <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center justify-center px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.activity-logs.export', array_merge(['format' => 'csv'], request()->query())) }}"
           class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </a>

        <form action="{{ route('admin.activity-logs.clear') }}" method="POST" class="inline">
            @csrf
            <div class="flex items-center gap-2">
                <select name="days" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="7">7 days</option>
                    <option value="15">15 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="60">60 days</option>
                    <option value="90">90 days</option>
                </select>
                <button type="submit" onclick="return confirm('Clear logs older than selected days?')" 
                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700">
                    <i class="fas fa-trash-alt mr-1"></i> Clear Old
                </button>
            </div>
        </form>

        <form action="{{ route('admin.activity-logs.clear-all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" onclick="return confirm('Are you sure you want to delete ALL logs?')" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">
                <i class="fas fa-trash mr-1"></i> Clear All
            </button>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="activityLogsTable">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">User</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Module</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Action</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Description</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">IP</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Date</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-2 px-2">
                                <span class="font-medium text-gray-900">{{ $log->user_name ?? 'System' }}</span>
                                <p class="text-xs text-gray-500">{{ $log->user_email ?? '' }}</p>
                            </td>
                            <td class="py-2 px-2">
                                <span class="flex items-center">
                                    <i class="fas {{ $log->module_icon }} text-gray-400 mr-2"></i>
                                    {{ $log->module_label }}
                                </span>
                            </td>
                            <td class="py-2 px-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log->action_color }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="py-2 px-2 text-sm text-gray-600">{{ $log->description }}</td>
                            <td class="py-2 px-2 text-sm text-gray-600">{{ $log->ip_address ?? '-' }}</td>
                            <td class="py-2 px-2 text-sm text-gray-600">{{ $log->formatted_date }}</td>
                            <td class="py-2 px-2 text-center">
                                <a href="{{ route('admin.activity-logs.show', $log) }}" class="text-blue-600 hover:underline text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('activityLogsTable')) {
            new DataTable('#activityLogsTable', {
                pageLength: 50,
                responsive: true,
                order: [[5, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });
        }
    });
</script>
@endpush
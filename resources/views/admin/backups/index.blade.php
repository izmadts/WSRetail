@extends('layouts.admin')

@section('title', 'Backup & Restore')
@section('page-title', 'Backup Management')

@section('content')
<div class="space-y-6">

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-database text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <form action="{{ route('admin.backups.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="database">
                        <button type="submit" class="text-left">
                            <h4 class="font-semibold text-gray-900">Database Backup</h4>
                            <p class="text-sm text-gray-500">Backup only database</p>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-archive text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <form action="{{ route('admin.backups.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="full">
                        <button type="submit" class="text-left">
                            <h4 class="font-semibold text-gray-900">Full Backup</h4>
                            <p class="text-sm text-gray-500">Database + Files</p>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-card-hover transition-shadow duration-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <!-- ✅ Use correct route name -->
                    <form action="{{ route('admin.backups.delete-all') }}" method="POST"
                        onsubmit="return confirm('⚠️ Are you sure you want to delete ALL backups? This action cannot be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-left">
                            <h4 class="font-semibold text-red-600">Delete All</h4>
                            <p class="text-sm text-gray-500">Remove all backups</p>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Backup List -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list text-gray-400 mr-2"></i> Backup Files
                <span class="ml-2 text-sm text-gray-500">{{ count($backups) }} files</span>
            </h4>
        </div>

        <div class="p-6">
            @if(count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full" id="backupsTable">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">File Name</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Type</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Created</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Size</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($backups as $backup)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2">
                                <span class="font-medium text-gray-900">{{ $backup['name'] }}</span>
                            </td>
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $backup['type'] == 'Database' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $backup['type'] }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600">
                                {{ $backup['created_at'] }}
                            </td>
                            <td class="py-3 px-2 text-right text-sm text-gray-600">
                                {{ $backup['size'] }}
                            </td>
                            <td class="py-3 px-2 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <a href="{{ route('admin.backups.download', $backup['name']) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-download text-sm"></i>
                                    </a>
                                    <form action="{{ route('admin.backups.restore', $backup['name']) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Restore from this backup? Current data will be overwritten!')"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-undo text-sm"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this backup?')"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-database text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500">No backups found. Create your first backup using the buttons above.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Storage Usage -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-server text-gray-400 mr-2"></i> Storage Usage
            </h4>
        </div>
        <div class="p-6">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                @php
                $totalSize = collect($backups)->sum(function($b) {
                return floatval(str_replace([' KB', ' MB', ' GB'], '', str_replace(',', '', $b['size'])));
                });
                $maxSize = 100; // MB
                $percentage = min(($totalSize / $maxSize) * 100, 100);
                @endphp
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-sm text-gray-500">
                <span>Used: {{ number_format($totalSize, 2) }} MB</span>
                <span>Limit: {{ $maxSize }} MB</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('backupsTable')) {
            new DataTable('#backupsTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [2, 'desc']
                ],
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
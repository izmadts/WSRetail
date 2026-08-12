@extends('layouts.admin')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')

@section('content')
<div x-data="{ showRejectModal: false, rejectId: null, openReject(id) { this.rejectId = id; this.showRejectModal = true; } }">
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700"><i class="fas fa-calendar-check text-gray-400 mr-2"></i> Leave Requests</span>
            <span class="ml-2 text-sm text-gray-500">{{ $leaveRequests->count() }} total</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.leave-requests.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !$status ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">All</a>
            <a href="{{ route('admin.leave-requests.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Pending</a>
            <a href="{{ route('admin.leave-requests.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $status === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Approved</a>
            <a href="{{ route('admin.leave-requests.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $status === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">Rejected</a>
            <a href="{{ route('admin.leave-types.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200">
                <i class="fas fa-list mr-1"></i> Leave Types
            </a>
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="leaveRequestsTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-2">Employee</th>
                        <th class="text-left py-3 px-2">Type</th>
                        <th class="text-left py-3 px-2">From</th>
                        <th class="text-left py-3 px-2">To</th>
                        <th class="text-center py-3 px-2">Days</th>
                        <th class="text-left py-3 px-2">Reason</th>
                        <th class="text-center py-3 px-2">Status</th>
                        <th class="text-center py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($leaveRequests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $req->employee->name ?? '-' }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $req->leaveType->name ?? '-' }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $req->from_date->format('d M Y') }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $req->to_date->format('d M Y') }}</td>
                        <td class="py-3 px-2 text-center">{{ $req->days_count }}</td>
                        <td class="py-3 px-2 text-gray-600">{{ $req->reason ?? '-' }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $req->status_color }}">
                                {{ $req->status_label }}
                            </span>
                            @if($req->status === 'rejected' && $req->rejection_reason)
                                <div class="text-xs text-gray-400 mt-1">{{ $req->rejection_reason }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-center">
                            @if($req->status === 'pending' && auth()->user()->hasPermission('leaves', 'edit'))
                            <div class="flex items-center justify-center space-x-1">
                                <form action="{{ route('admin.leave-requests.approve', $req) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Approve this leave request?')" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                </form>
                                <button type="button" @click="openReject({{ $req->id }})" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                            @else
                                <span class="text-xs text-gray-400">{{ $req->approvedBy->name ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Reject Leave Request</h3>
                <button @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form :action="'{{ url('admin/leave-requests') }}/' + rejectId + '/reject'" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                        <textarea name="rejection_reason" rows="3" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                            <i class="fas fa-times mr-1"></i> Reject
                        </button>
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('leaveRequestsTable')) {
            new DataTable('#leaveRequestsTable', {
                pageLength: 25,
                responsive: true,
                order: [[0, 'asc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    emptyTable: "No leave requests found."
                }
            });
        }
    });
</script>
@endpush

@extends('layouts.admin')

@section('title', 'My Leave')
@section('page-title', 'My Leave')

@section('content')
<div x-data="{ showModal: false }">

@if(!$employee)
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 mb-4">
    No employee record was found for your account - contact an admin.
</div>
@else

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($balances as $b)
    <div class="bg-white rounded-xl shadow-card p-4">
        <p class="text-sm text-gray-500">{{ $b['leave_type']->name }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $b['remaining'] }} <span class="text-sm font-normal text-gray-400">/ {{ $b['leave_type']->default_days_per_year }} days left</span></p>
        <p class="text-xs text-gray-400 mt-1">{{ $b['used'] }} used this year</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <span class="text-sm font-medium text-gray-700"><i class="fas fa-calendar-day text-gray-400 mr-2"></i> My Leave Requests</span>
        <button type="button" @click="showModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-plus mr-1"></i> Request Leave
        </button>
    </div>
    <div class="p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
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
                    @forelse($myRequests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium text-gray-900">{{ $req->leaveType->name ?? '-' }}</td>
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
                            @if($req->status === 'pending')
                            <form action="{{ route('admin.my-leave.cancel', $req) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Cancel this leave request?')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Request Leave Modal -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-[rgba(0,0,0,.5)]" @click="showModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Request Leave</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.my-leave.store') }}" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type <span class="text-red-500">*</span></label>
                        <select name="leave_type_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From <span class="text-red-500">*</span></label>
                            <input type="date" name="from_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To <span class="text-red-500">*</span></label>
                            <input type="date" name="to_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea name="reason" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-paper-plane mr-1"></i> Submit
                        </button>
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endif
</div>
@endsection

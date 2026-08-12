@extends('layouts.admin')

@section('title', 'Agent Management')
@section('page-title', 'Agent Management')

@section('content')
<div class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
        <div>
            <span class="text-sm font-medium text-gray-700">
                <i class="fas fa-users text-gray-400 mr-2"></i> All Agents
            </span>
            <span class="ml-2 text-sm text-gray-500">{{ $agents->count() }} total</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.agents.pending') }}" 
               class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-medium hover:bg-yellow-700 transition-colors duration-200">
                <i class="fas fa-clock mr-1"></i> Pending Approvals
                @php $pending = App\Models\User::where('role', 'sales_agent')->where('is_active', false)->whereNull('approved_at')->count(); @endphp
                @if($pending > 0)
                    <span class="ml-1 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pending }}</span>
                @endif
            </a>
            <a href="{{ route('admin.agents.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Add Agent
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full" id="agentsTable">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Name</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Email</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Phone</th>
                        <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Basic Salary</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Status</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-3 px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($agents as $agent)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="py-3 px-2">
                            <span class="font-medium text-gray-900">{{ $agent->name }}</span>
                        </td>
                        <td class="py-3 px-2 text-sm text-gray-600">{{ $agent->email }}</td>
                        <td class="py-3 px-2 text-sm text-gray-600">{{ $agent->phone ?? '-' }}</td>
                        <td class="py-3 px-2 text-right">Rs. {{ number_format($agent->basic_salary, 2) }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $agent->status_color }}">
                                {{ $agent->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.agents.view', $agent) }}" 
                                   class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.agents.edit', $agent) }}" 
                                   class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <a href="{{ route('admin.agents.approve', $agent) }}" 
                                   class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-check-circle text-sm"></i>
                                </a>
                                <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" 
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('agentsTable')) {
            new DataTable('#agentsTable', {
                pageLength: 25,
                responsive: true,
                order: [[0, 'asc']],
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
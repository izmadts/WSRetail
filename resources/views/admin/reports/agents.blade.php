@extends('layouts.admin')

@section('title', 'Agents Report')
@section('page-title', 'Agents Performance Report')

@section('content')
<div class="space-y-6">
    @include('admin.partials.export-buttons', ['type' => 'agents'])
    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Agents</p>
            <p class="text-2xl font-bold">{{ $agents->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Active Agents</p>
            <p class="text-2xl font-bold text-green-600">{{ $agents->where('is_active', true)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Sales</p>
            <p class="text-2xl font-bold text-blue-600">Rs. {{ number_format($agents->sum('total_sales'), 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-card p-4">
            <p class="text-sm text-gray-500">Total Commission</p>
            <p class="text-2xl font-bold text-purple-600">Rs. {{ number_format($agents->sum('total_commission'), 2) }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="agentsReportTable">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Agent</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Email</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Customers</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Total Sales</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Commission</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Status</th>
                            <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $agent)
                        <tr>
                            <td class="py-2 px-2 font-medium">{{ $agent->name }}</td>
                            <td class="py-2 px-2">{{ $agent->email }}</td>
                            <td class="py-2 px-2 text-right">{{ $agent->total_customers }}</td>
                            <td class="py-2 px-2 text-right text-blue-600">Rs. {{ number_format($agent->total_sales, 2) }}</td>
                            <td class="py-2 px-2 text-right text-purple-600">Rs. {{ number_format($agent->total_commission, 2) }}</td>
                            <td class="py-2 px-2 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $agent->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-2 px-2 text-center">
                                <a href="{{ route('admin.reports.agent-detail', $agent) }}" class="text-blue-600 hover:underline text-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>                        
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('agentsReportTable')) {
            new DataTable('#agentsReportTable', {
                pageLength: 25,
                responsive: true,
                order: [
                    [0, 'asc']
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
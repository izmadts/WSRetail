@extends('layouts.admin')

@section('title', 'Reconcile All Accounts')
@section('page-title', 'Reconcile All Accounts (Ledger Integrity)')

@section('content')
<div class="space-y-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Scans every accounting transaction in the system for missing, duplicate, orphaned, unbalanced, or mismatched ledger entries, and can repair the safe cases automatically. Unbalanced entries, confirmed-orphaned entries, and broken account references are <strong>never</strong> auto-fixed - they're flagged for manual review only. Every fix applied here is written to the Activity Log with a full before/after snapshot of the changed ledger rows.
    </div>

    <div class="bg-white rounded-xl shadow-card p-6 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Ledger Integrity Scan</h3>
            <p class="text-sm text-gray-500">Not the same as Bank Reconciliation - this checks the ledger's own internal consistency, not a bank statement.</p>
        </div>
        <form action="{{ route('admin.ledger-integrity.scan') }}" method="POST">
            @csrf
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                <i class="fas fa-search mr-1"></i> Run Scan
            </button>
        </form>
    </div>

    @if($results)
        @php
            $sev = $results['summary']['by_severity'];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $results['summary']['total'] }}</p>
                <p class="text-xs text-gray-500">Total Issues</p>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $results['summary']['fixable'] }}</p>
                <p class="text-xs text-gray-500">Auto-fixable</p>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ $sev['critical'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">Critical</p>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <p class="text-2xl font-bold text-orange-600">{{ $sev['high'] ?? 0 }}</p>
                <p class="text-xs text-gray-500">High</p>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ ($sev['medium'] ?? 0) + ($sev['low'] ?? 0) }}</p>
                <p class="text-xs text-gray-500">Medium / Low</p>
            </div>
        </div>

        @if(count($results['issues']) === 0)
            <div class="bg-white rounded-xl shadow-card p-10 text-center">
                <i class="fas fa-check-circle text-4xl text-green-500 mb-3 block"></i>
                <p class="text-gray-600">No issues found - the ledger is clean.</p>
            </div>
        @else
            @php
                $safeIssues = collect($results['issues'])->where('fix_group', 'safe')->values();
            @endphp

            @if($safeIssues->count() > 0)
            <form action="{{ route('admin.ledger-integrity.fix') }}" method="POST"
                  onsubmit="return confirm('Apply {{ $safeIssues->count() }} safe fix(es)? Each one is logged to the Activity Log with a full before/after snapshot.')">
                @csrf
                @foreach($safeIssues as $i => $issue)
                    <input type="hidden" name="selectors[{{ $i }}][reference_type]" value="{{ $issue['reference_type'] }}">
                    <input type="hidden" name="selectors[{{ $i }}][reference_id]" value="{{ $issue['reference_id'] }}">
                    <input type="hidden" name="selectors[{{ $i }}][category]" value="{{ $issue['category'] }}">
                @endforeach
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-magic mr-1"></i> Fix All Safe Issues ({{ $safeIssues->count() }})
                </button>
            </form>
            @endif

            @foreach(['critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $sevKey => $sevLabel)
                @php $group = collect($results['issues'])->where('severity', $sevKey)->values(); @endphp
                @if($group->count() > 0)
                <div class="bg-white rounded-xl shadow-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h4 class="font-semibold text-gray-900">{{ $sevLabel }} <span class="text-sm font-normal text-gray-500">({{ $group->count() }})</span></h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="text-left py-2 px-3">Category</th>
                                    <th class="text-left py-2 px-3">Entity</th>
                                    <th class="text-left py-2 px-3">Description</th>
                                    <th class="text-center py-2 px-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($group as $issue)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ ucfirst(str_replace('_', ' ', $issue['category'])) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 font-medium text-gray-900 whitespace-nowrap">{{ $issue['entity_label'] }}</td>
                                    <td class="py-2 px-3 text-gray-600">{{ $issue['description'] }}</td>
                                    <td class="py-2 px-3 text-center whitespace-nowrap">
                                        @if($issue['fix_group'] === 'manual_only')
                                            <span class="text-xs text-gray-400">Manual review</span>
                                        @else
                                            <form action="{{ route('admin.ledger-integrity.fix') }}" method="POST"
                                                  onsubmit="return confirm('Apply this fix? It will be logged to the Activity Log with a full before/after snapshot.')" class="inline">
                                                @csrf
                                                <input type="hidden" name="selectors[0][reference_type]" value="{{ $issue['reference_type'] }}">
                                                <input type="hidden" name="selectors[0][reference_id]" value="{{ $issue['reference_id'] }}">
                                                <input type="hidden" name="selectors[0][category]" value="{{ $issue['category'] }}">
                                                <button type="submit"
                                                    class="px-3 py-1 {{ $issue['fix_group'] === 'confirm_required' ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg text-xs font-medium transition-colors duration-200">
                                                    Fix
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach
        @endif
    @endif

    @if(isset($fixOutcomes))
    <div class="bg-white rounded-xl shadow-card p-6">
        <h4 class="font-semibold text-gray-900 mb-3">Last Fix Run</h4>
        <ul class="text-sm space-y-1">
            @foreach($fixOutcomes as $outcome)
            <li>
                <span class="font-medium">{{ $outcome['selector']['reference_type'] }} #{{ $outcome['selector']['reference_id'] }}</span>
                -
                <span class="{{ $outcome['status'] === 'fixed' ? 'text-green-600' : ($outcome['status'] === 'failed' ? 'text-red-600' : 'text-gray-500') }}">
                    {{ ucfirst(str_replace('_', ' ', $outcome['status'])) }}
                </span>
                @if(isset($outcome['error']))
                    <span class="text-red-500"> - {{ $outcome['error'] }}</span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection

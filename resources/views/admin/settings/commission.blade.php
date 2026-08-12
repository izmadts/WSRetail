@extends('layouts.admin')

@section('title', 'Settings - Commission & Bonus Policy')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

@php
    $cashTiersForJs = $settings['commission.cash_tiers'];
    $targetTiersForJs = $settings['commission.target_bonus_tiers'];
@endphp

<div x-data="commissionSettings()" class="bg-white rounded-xl shadow-card overflow-hidden max-w-4xl">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-percentage text-blue-600 mr-2"></i> Commission &amp; Bonus Policy</h4>
        <p class="text-sm text-gray-500 mt-1">Matches the sales/marketing commission policy - editable here instead of hardcoded.</p>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.settings.commission.update') }}" method="POST">
            @csrf
            <div class="space-y-8">

                <!-- Cash Sale Commission Tiers -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h5 class="text-sm font-semibold text-gray-800">Cash Sale Commission Tiers</h5>
                        <button type="button" @click="addCashTier()" class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"><i class="fas fa-plus mr-1"></i>Add Tier</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Rate applied is based on the agent's cumulative cash sales so far this month, including the current sale.</p>
                    <template x-for="(tier, i) in cashTiers" :key="i">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-gray-500 w-16">From</span>
                            <input type="number" step="0.01" :name="'cash_tiers['+i+'][from]'" x-model="tier.from" class="w-32 px-2 py-1.5 text-sm border border-gray-300 rounded-lg" required>
                            <span class="text-xs text-gray-500 w-8">To</span>
                            <input type="number" step="0.01" :name="'cash_tiers['+i+'][to]'" x-model="tier.to" placeholder="∞" class="w-32 px-2 py-1.5 text-sm border border-gray-300 rounded-lg">
                            <span class="text-xs text-gray-500 w-10">Rate %</span>
                            <input type="number" step="0.01" :name="'cash_tiers['+i+'][rate]'" x-model="tier.rate" class="w-24 px-2 py-1.5 text-sm border border-gray-300 rounded-lg" required>
                            <button type="button" @click="cashTiers.splice(i, 1)" class="text-red-500 hover:text-red-700 p-1"><i class="fas fa-times"></i></button>
                        </div>
                    </template>
                </div>

                <hr>

                <!-- Credit Sale Commission -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Credit Sale Commission Rate (%)</label>
                        <input type="number" step="0.01" name="credit_rate" value="{{ old('credit_rate', $settings['commission.credit_rate']) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Applied to every payment recorded against a credit sale, as it's recovered - not held until fully settled.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Credit Hold Grace Period (days)
                            <x-help-tooltip>How many days a credit sale can sit unpaid before it counts as "overdue" - the default for every customer, unless a specific customer has their own Credit Days set on their profile (that overrides this number for them only). Combined with "Block new credit sales" below to decide whether overdue customers can still buy on credit.</x-help-tooltip>
                        </label>
                        <input type="number" name="credit_hold_grace_days" value="{{ old('credit_hold_grace_days', $settings['commission.credit_hold_grace_days']) }}" required min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row flex-wrap gap-4">
                    <div class="flex items-start">
                        <label class="flex items-center">
                            <input type="checkbox" name="enforce_credit_block" value="1" {{ old('enforce_credit_block', $settings['commission.enforce_credit_block']) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Block new credit sales to customers overdue past the grace period</span>
                        </label>
                        <x-help-tooltip>When on, a new credit sale is refused (with an explanation) for any customer who has an unpaid credit sale older than their grace period. When off, overdue customers can still buy on credit - nothing here stops them, it's purely informational elsewhere in the system.</x-help-tooltip>
                    </div>
                    <div class="flex items-start">
                        <label class="flex items-center">
                            <input type="checkbox" name="enforce_credit_limit" value="1" {{ old('enforce_credit_limit', $settings['commission.enforce_credit_limit']) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Block new credit sales that would exceed a customer's Credit Limit</span>
                        </label>
                        <x-help-tooltip>When on, a new credit sale is refused if it would push a customer's outstanding balance above their own Credit Limit field (set on their customer profile). Customers with Credit Limit left at 0 have no limit and are never blocked by this.</x-help-tooltip>
                    </div>
                    <label class="flex items-center">
                        <input type="checkbox" name="require_customer_verification" value="1" {{ old('require_customer_verification', $settings['commission.require_customer_verification']) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">No commission on sales to unregistered/inactive agent-created customers</span>
                    </label>
                </div>

                <hr>

                <!-- New Customer Bonus -->
                <div>
                    <h5 class="text-sm font-semibold text-gray-800 mb-2">
                        New Customer Bonus
                        <x-help-tooltip>Paid to the AGENT (not the customer) once a customer they personally registered reaches the minimum order count below - a one-time reward per customer for growing your customer base, not a discount for the customer.</x-help-tooltip>
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bonus Amount</label>
                            <input type="number" step="0.01" name="new_customer_bonus_amount" value="{{ old('new_customer_bonus_amount', $settings['commission.new_customer_bonus_amount']) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Orders Required</label>
                            <input type="number" name="new_customer_bonus_min_orders" value="{{ old('new_customer_bonus_min_orders', $settings['commission.new_customer_bonus_min_orders']) }}" required min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Recovery Bonus -->
                <div>
                    <h5 class="text-sm font-semibold text-gray-800 mb-2">
                        Recovery Bonus
                        <x-help-tooltip>Paid to the AGENT as an extra one-time reward on a specific credit sale once its recovery rate (how much of it has actually been collected) crosses the threshold - encourages agents to chase down full payment on credit sales, not just make them.</x-help-tooltip>
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recovery Threshold (%)</label>
                            <input type="number" step="0.01" name="recovery_bonus_threshold_pct" value="{{ old('recovery_bonus_threshold_pct', $settings['commission.recovery_bonus_threshold_pct']) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bonus Rate (%)</label>
                            <input type="number" step="0.01" name="recovery_bonus_rate_pct" value="{{ old('recovery_bonus_rate_pct', $settings['commission.recovery_bonus_rate_pct']) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Monthly Target Bonus Tiers -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h5 class="text-sm font-semibold text-gray-800">Monthly Target Bonus Tiers</h5>
                        <button type="button" @click="addTargetTier()" class="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"><i class="fas fa-plus mr-1"></i>Add Tier</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Posted when an admin closes the month (Agents &gt; agent &gt; Close Month, or <code>php artisan commission:close-month</code>).</p>
                    <template x-for="(tier, i) in targetTiers" :key="i">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-gray-500 w-24">Achievement %</span>
                            <input type="number" step="0.01" :name="'target_bonus_tiers['+i+'][achievement_pct]'" x-model="tier.achievement_pct" class="w-32 px-2 py-1.5 text-sm border border-gray-300 rounded-lg" required>
                            <span class="text-xs text-gray-500 w-14">Bonus</span>
                            <input type="number" step="0.01" :name="'target_bonus_tiers['+i+'][bonus]'" x-model="tier.bonus" class="w-32 px-2 py-1.5 text-sm border border-gray-300 rounded-lg" required>
                            <button type="button" @click="targetTiers.splice(i, 1)" class="text-red-500 hover:text-red-700 p-1"><i class="fas fa-times"></i></button>
                        </div>
                    </template>
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Save Policy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function commissionSettings() {
    return {
        cashTiers: @json($cashTiersForJs),
        targetTiers: @json($targetTiersForJs),
        addCashTier() {
            this.cashTiers.push({ from: 0, to: null, rate: 0 });
        },
        addTargetTier() {
            this.targetTiers.push({ achievement_pct: 0, bonus: 0 });
        },
    }
}
</script>
@endsection

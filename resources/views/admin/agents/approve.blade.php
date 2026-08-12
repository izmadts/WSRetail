@extends('layouts.admin')

@section('title', 'Approve Agent')
@section('page-title', 'Approve Agent: ' . $user->name)

@section('content')
<div x-data="slabForm()" class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-check-circle text-green-600 mr-2"></i> Approve Agent
        </h3>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.agents.do-approve', $user) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Basic Salary -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', 0) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Allowance <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="fuel_allowance" value="{{ old('fuel_allowance', 0) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (Credit) <span class="text-red-500">*</span></label>
                    <div class="flex items-center">
                        <input type="number" step="0.01" name="commission_rate_credit" value="{{ old('commission_rate_credit', 1) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               min="0" max="100">
                        <span class="ml-2 text-gray-600">%</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Channel <span class="text-red-500">*</span></label>
                    <select name="channel" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="both" {{ old('channel', 'both') == 'both' ? 'selected' : '' }}>Both (Wholesale + Retail)</option>
                        <option value="wholesale" {{ old('channel') == 'wholesale' ? 'selected' : '' }}>Wholesale only</option>
                        <option value="retail" {{ old('channel') == 'retail' ? 'selected' : '' }}>Retail only</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Restricts which customers/products this agent's app can see and sell to.</p>
                </div>
            </div>

            <!-- Commission Slabs for Cash Sales -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-md font-semibold text-gray-900">
                        <i class="fas fa-layer-group text-blue-600 mr-2"></i> Cash Sales Commission Slabs
                    </h4>
                    <button type="button" @click="addSlab()" 
                            class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-1"></i> Add Slab
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[500px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">From (Rs.)</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">To (Rs.)</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2">Rate (%)</th>
                                <th class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider py-2 px-2 w-12">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(slab, index) in slabs" :key="index">
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 px-2">
                                        <input type="number" step="0.01" 
                                               :name="'slabs['+index+'][from]'" 
                                               x-model="slab.from"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               min="0" step="0.01" placeholder="0">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" step="0.01" 
                                               :name="'slabs['+index+'][to]'" 
                                               x-model="slab.to"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               min="0" step="0.01" placeholder="∞ (leave empty)">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" step="0.01" 
                                               :name="'slabs['+index+'][rate]'" 
                                               x-model="slab.rate"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               min="0" max="100" step="0.01" placeholder="1">
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        <button type="button" @click="removeSlab(index)" 
                                                class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded-lg transition-colors duration-200">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="slabs.length === 0" class="text-center py-4 text-gray-400">
                    <p class="text-sm">No slabs defined. Add a slab to set commission tiers.</p>
                </div>

                <!-- Default Slabs Hint -->
                <div class="mt-2 p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Default Slabs</strong> (from <a href="{{ route('admin.settings.commission') }}" class="underline" target="_blank">Settings &gt; Commission &amp; Bonus Policy</a>):
                    <template x-for="(slab, index) in defaultSlabs" :key="index">
                        <span> <span x-text="formatSlabRange(slab)"></span> → <span x-text="slab.rate"></span>%<span x-show="index < defaultSlabs.length - 1"> |</span></span>
                    </template>
                    <button type="button" @click="loadDefaultSlabs()" class="ml-2 text-blue-600 hover:underline">Load Default</button>
                </div>
            </div>

            <!-- Admin Note -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                <textarea name="admin_note" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('admin_note') }}</textarea>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-check mr-1"></i> Approve Agent
                </button>
                <a href="{{ route('admin.agents.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function slabForm() {
    return {
        slabs: [],
        // Sourced from the real, admin-editable Commission & Bonus Policy
        // (Settings > Commission & Bonus Policy) - not a hardcoded copy, so
        // it never drifts from what the org has actually configured.
        defaultSlabs: @json($defaultCashTiers ?? []),
        init() {
            this.loadDefaultSlabs();
        },
        addSlab() {
            this.slabs.push({ from: '', to: '', rate: '' });
        },
        removeSlab(index) {
            if (this.slabs.length > 1) {
                this.slabs.splice(index, 1);
            }
        },
        loadDefaultSlabs() {
            this.slabs = this.defaultSlabs.map(s => ({ from: s.from, to: s.to, rate: s.rate }));
        },
        formatSlabRange(slab) {
            const from = Number(slab.from).toLocaleString();
            return slab.to ? `${from}-${Number(slab.to).toLocaleString()}` : `${from}+`;
        }
    }
}
</script>
@endsection
@extends('layouts.admin')

@section('title', 'Edit Agent')
@section('page-title', 'Edit Agent: ' . $user->name)

@section('content')
<div x-data="slabForm()" class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-edit text-yellow-600 mr-2"></i> Edit Agent
        </h3>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.agents.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
                    <div x-data="{ showPassword: false }" class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password"
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-yellow-600">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div x-data="{ showPassword: false }" class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password_confirmation"
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-yellow-600">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" value="{{ old('cnic', $user->cnic) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('address', $user->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary</label>
                    <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', $user->basic_salary) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Allowance</label>
                    <input type="number" step="0.01" name="fuel_allowance" value="{{ old('fuel_allowance', $user->fuel_allowance) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (Cash) %</label>
                    <input type="number" step="0.01" name="commission_rate_cash" value="{{ old('commission_rate_cash', $user->commission_rate_cash) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           min="0" max="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (Credit) %</label>
                    <input type="number" step="0.01" name="commission_rate_credit" value="{{ old('commission_rate_credit', $user->commission_rate_credit) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                           min="0" max="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Channel</label>
                    <select name="channel"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="both" {{ old('channel', $user->channel ?? 'both') == 'both' ? 'selected' : '' }}>Both (Wholesale + Retail)</option>
                        <option value="wholesale" {{ old('channel', $user->channel) == 'wholesale' ? 'selected' : '' }}>Wholesale only</option>
                        <option value="retail" {{ old('channel', $user->channel) == 'retail' ? 'selected' : '' }}>Retail only</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Restricts which customers/products this agent's app can see and sell to.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                    <textarea name="admin_note" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('admin_note', $user->admin_note) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Active</span>
                    </label>
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

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Update Agent
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
        // Starts from whatever's already saved for this agent; falls back
        // to the org-wide default (Settings > Commission & Bonus Policy)
        // only if the agent has no slabs of their own yet.
        slabs: @json(!empty($user->commission_slabs) ? $user->commission_slabs : ($defaultCashTiers ?? [])),
        defaultSlabs: @json($defaultCashTiers ?? []),
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
@extends('layouts.admin')

@section('title', 'Add Agent')
@section('page-title', 'Create New Agent')

@section('content')
<div x-data="slabForm()" class="bg-white rounded-xl shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-user-plus text-blue-600 mr-2"></i> New Agent
        </h3>
    </div>

    <div class="p-6">
        <form action="{{ route('admin.agents.store') }}" method="POST">
            @csrf

            <!-- ========================================== -->
            <!-- PERSONAL INFORMATION -->
            <!-- ========================================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <div x-data="{ showPassword: false }" class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <div x-data="{ showPassword: false }" class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" required
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" @click="showPassword = !showPassword" tabindex="-1" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                    <input type="text" name="cnic" value="{{ old('cnic') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('cnic')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- ========================================== -->
            <!-- FINANCIAL SETTINGS -->
            <!-- ========================================== -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3">
                    <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i> Financial Settings
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary</label>
                        <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="0" step="0.01">
                        @error('basic_salary')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Allowance</label>
                        <input type="number" step="0.01" name="fuel_allowance" value="{{ old('fuel_allowance', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="0" step="0.01">
                        @error('fuel_allowance')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (Credit) %</label>
                        <input type="number" step="0.01" name="commission_rate_credit" value="{{ old('commission_rate_credit', 1) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            min="0" max="100">
                        @error('commission_rate_credit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Channel</label>
                        <select name="channel"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="both" {{ old('channel', 'both') == 'both' ? 'selected' : '' }}>Both (Wholesale + Retail)</option>
                            <option value="wholesale" {{ old('channel') == 'wholesale' ? 'selected' : '' }}>Wholesale only</option>
                            <option value="retail" {{ old('channel') == 'retail' ? 'selected' : '' }}>Retail only</option>
                        </select>
                        @error('channel')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- COMMISSION SLABS (Cash Sales) -->
            <!-- ========================================== -->
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
                    <strong>Default Slabs:</strong> 0-300,000 → 1% | 300,001-700,000 → 1.5% | 700,001-1,500,000 → 2% | 1,500,000+ → 2.5%
                    <button type="button" @click="loadDefaultSlabs()" class="ml-2 text-blue-600 hover:underline font-medium">Load Default</button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- ADMIN NOTE & STATUS -->
            <!-- ========================================== -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                        <textarea name="admin_note" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('admin_note') }}</textarea>
                        @error('admin_note')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SUBMIT BUTTONS -->
            <!-- ========================================== -->
            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Create Agent
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
            init() {
                // Load default slabs
                this.loadDefaultSlabs();
            },
            addSlab() {
                this.slabs.push({
                    from: '',
                    to: '',
                    rate: ''
                });
            },
            removeSlab(index) {
                if (this.slabs.length > 1) {
                    this.slabs.splice(index, 1);
                }
            },
            loadDefaultSlabs() {
                this.slabs = [{
                        from: 0,
                        to: 300000,
                        rate: 1
                    },
                    {
                        from: 300001,
                        to: 700000,
                        rate: 1.5
                    },
                    {
                        from: 700001,
                        to: 1500000,
                        rate: 2
                    },
                    {
                        from: 1500001,
                        to: null,
                        rate: 2.5
                    },
                ];
            }
        }
    }
</script>
@endsection
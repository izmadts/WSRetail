@props(['disabled' => false])

@if(($attributes->get('type') ?? 'text') === 'password')
<div x-data="{ showPassword: false }" class="relative">
    <input @disabled($disabled) :type="showPassword ? 'text' : 'password'"
        {{ $attributes->except('type')->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-10']) }}>
    <button type="button" @click="showPassword = !showPassword" tabindex="-1"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-blue-600">
        <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
    </button>
</div>
@else
<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
@endif

@extends('layouts.admin')

@section('title', 'Settings - General')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings.partials.tabs')

<div class="bg-white rounded-xl shadow-card overflow-hidden max-w-3xl">
    <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="fas fa-cog text-blue-600 mr-2"></i> General Settings</h4>
    </div>
    <div class="p-6">
        <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Application Name <span class="text-red-500">*</span></label>
                        <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('app_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <input type="file" name="logo" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @if($settings['logo'])
                        <img src="{{ asset($settings['logo']) }}" alt="Current logo" class="mt-2 h-10">
                        @endif
                        @error('logo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Favicon
                            <x-help-tooltip>The small icon shown in the browser tab. Accepts .ico, .png, .jpg, or .svg - a square image (e.g. 32x32 or 64x64) works best. Separate from the Logo above, which is the larger image shown inside the app itself.</x-help-tooltip>
                        </label>
                        <input type="file" name="favicon" accept=".ico,image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @if($settings['favicon'])
                        <img src="{{ asset($settings['favicon']) }}" alt="Current favicon" class="mt-2 h-8 w-8 object-contain">
                        @endif
                        @error('favicon')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency Code <span class="text-red-500">*</span></label>
                        <input type="text" name="currency_code" value="{{ old('currency_code', $settings['currency_code']) }}" required maxlength="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol <span class="text-red-500">*</span></label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required maxlength="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Note: many existing pages still show "Rs." directly - full rollout of this symbol everywhere is a follow-up.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Format <span class="text-red-500">*</span></label>
                        <select name="date_format" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['d-m-Y' => 'DD-MM-YYYY', 'm-d-Y' => 'MM-DD-YYYY', 'Y-m-d' => 'YYYY-MM-DD'] as $value => $label)
                            <option value="{{ $value }}" {{ old('date_format', $settings['date_format']) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Timezone <span class="text-red-500">*</span>
                        <x-help-tooltip>Governs every date/time calculation in the system from the moment you save it - now(), due dates, report ranges, scheduled jobs. Changing this changes what "today" means for everyone using the system, not just how dates are displayed.</x-help-tooltip>
                    </label>
                    <select name="timezone" required class="w-full sm:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($timezones as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $settings['timezone']) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    @error('timezone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Theme Color</label>
                    <div class="flex flex-wrap gap-3" x-data="{ selected: '{{ old('theme_color', $settings['theme_color']) }}' }">
                        @foreach($themes as $key => $theme)
                        <label class="cursor-pointer">
                            <input type="radio" name="theme_color" value="{{ $key }}" x-model="selected" class="sr-only peer">
                            <span class="flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition-colors duration-150"
                                :class="selected === '{{ $key }}' ? 'border-gray-800' : 'border-gray-200 hover:border-gray-300'">
                                <span class="w-5 h-5 rounded-full inline-block" style="background-color: {{ $theme['swatch'] }}"></span>
                                <span class="text-sm text-gray-700">{{ $theme['label'] }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Sets the accent color used throughout the admin panel (buttons, links, highlights).</p>
                    @error('theme_color')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="dark_mode_enabled" value="1"
                            {{ old('dark_mode_enabled', $settings['dark_mode_enabled']) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Allow dark mode</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">When enabled, everyone gets a light/dark toggle in the top bar (their own choice, remembered on their own device). When disabled, the toggle is hidden and the panel stays light for everyone.</p>
                </div>

                <div class="border-t border-gray-200 pt-4" x-data="{ demo: {{ old('demo_mode', $settings['demo_mode']) ? 'true' : 'false' }} }">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="demo_mode" value="1" x-model="demo"
                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm font-medium text-gray-700">Demo mode</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">
                        Shows a credentials hint on the login page, with the email/password fields already filled in
                        so a visitor can just click Sign In. Only turn this on for a public demo install (like
                        demo.izmadts.com) that visitors are meant to log into freely - <strong class="text-amber-700">
                        never enable this on a real customer's live business data.</strong>
                    </p>
                    <div x-show="demo" x-cloak class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Demo Login Email</label>
                            <input type="email" name="demo_email" value="{{ old('demo_email', $settings['demo_email']) }}"
                                placeholder="admin@demo.com" maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @error('demo_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Demo Login Password</label>
                            <input type="text" name="demo_password" value="{{ old('demo_password', $settings['demo_password']) }}"
                                placeholder="e.g. demoAdmin321" maxlength="255" minlength="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">
                            @error('demo_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <p class="sm:col-span-2 text-xs text-gray-500">
                            Saving this actually creates/updates a real admin account with these exact credentials -
                            not just display text - so what's shown on the login page always works. Fill in both or
                            leave both blank (a half-filled pair is rejected).
                        </p>
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-save mr-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

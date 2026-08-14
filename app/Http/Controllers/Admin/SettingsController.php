<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CustomerCreditService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function general()
    {
        $settings = [
            'app_name' => Setting::get('app_name', config('app.name')),
            'currency_code' => Setting::get('currency_code', 'PKR'),
            'currency_symbol' => Setting::get('currency_symbol', 'Rs.'),
            'logo' => Setting::get('logo'),
            'favicon' => Setting::get('favicon'),
            'timezone' => Setting::get('timezone', config('app.timezone')),
            'date_format' => Setting::get('date_format', 'd-m-Y'),
            'theme_color' => Setting::get('theme_color', config('themes.default')),
            // Stored as the literal strings "1"/"0" (see updateGeneral()) -
            // matches how the View::composer in AppServiceProvider reads it.
            'dark_mode_enabled' => Setting::get('dark_mode_enabled', '1') === '1',
            // Demo Mode: shows a credentials hint on the login page. Only
            // meant for the owner's own public demo install - see the
            // warning next to the toggle in the view.
            'demo_mode' => Setting::get('demo_mode', '0') === '1',
            'demo_email' => Setting::get('demo_email', ''),
            'demo_password' => Setting::get('demo_password', ''),
        ];

        // DateTimeZone::listIdentifiers() is PHP's own canonical, always-
        // current list of valid IANA timezone names - avoids hand-maintaining
        // one that drifts out of date, and guarantees whatever is selected
        // is something PHP/Carbon can actually parse.
        $timezones = \DateTimeZone::listIdentifiers();
        $themes = config('themes.presets');

        return view('admin.settings.general', compact('settings', 'timezones', 'themes'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'currency_code' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'timezone' => 'required|string|max:100|timezone',
            'date_format' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:1024',
            // No 'image' rule here - PHP's getimagesize() (which that rule
            // relies on) doesn't reliably recognize .ico files, which would
            // wrongly reject the single most common real favicon format.
            'favicon' => 'nullable|mimes:ico,png,jpg,jpeg,svg|max:512',
            'theme_color' => 'required|string|in:' . implode(',', array_keys(config('themes.presets'))),
            'dark_mode_enabled' => 'nullable|boolean',
            'demo_mode' => 'nullable|boolean',
            // Both required together, or both left blank - a half-filled
            // pair (e.g. an email with no password) would show a hint on
            // the login page for an account that either doesn't exist or
            // has some other password, exactly the confusion this replaces
            // a single free-text note with.
            'demo_email' => 'nullable|required_with:demo_password|email|max:255',
            'demo_password' => 'nullable|required_with:demo_email|string|min:6|max:255',
        ]);

        foreach (['app_name', 'currency_code', 'currency_symbol', 'timezone', 'date_format', 'theme_color'] as $key) {
            Setting::set($key, $validated[$key]);
        }
        Setting::set('dark_mode_enabled', $request->boolean('dark_mode_enabled') ? '1' : '0');
        Setting::set('demo_mode', $request->boolean('demo_mode') ? '1' : '0');
        Setting::set('demo_email', $validated['demo_email'] ?? '');
        Setting::set('demo_password', $validated['demo_password'] ?? '');

        // The whole point of these two fields: saving them doesn't just
        // change what the login page DISPLAYS, it actually provisions/
        // updates a real, active, approved admin account with those exact
        // credentials - so what a demo visitor is told can never drift out
        // of sync with what actually works (see the demo.izmadts.com
        // incident this replaced the old free-text note over). Runs
        // whenever both fields are present, independent of whether Demo
        // Mode itself is currently toggled on, so an admin can prepare the
        // account ahead of flipping the toggle. Changing the demo email
        // later does NOT delete/deactivate the previous one - intentional,
        // since it may have real demo activity tied to it as created_by
        // elsewhere; manage/deactivate that old account by hand if needed.
        if (!empty($validated['demo_email']) && !empty($validated['demo_password'])) {
            \App\Models\User::updateOrCreate(
                ['email' => $validated['demo_email']],
                [
                    'name' => 'Demo Admin',
                    'password' => bcrypt($validated['demo_password']),
                    'role' => 'admin',
                    'is_active' => true,
                    'approved_at' => now(),
                ]
            );
        }

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo && file_exists(public_path($oldLogo))) {
                unlink(public_path($oldLogo));
            }
            $path = $request->file('logo')->store('uploads/settings', 'public');
            Setting::set('logo', 'storage/' . $path);
        }

        if ($request->hasFile('favicon')) {
            $oldFavicon = Setting::get('favicon');
            if ($oldFavicon && file_exists(public_path($oldFavicon))) {
                unlink(public_path($oldFavicon));
            }
            $path = $request->file('favicon')->store('uploads/settings', 'public');
            Setting::set('favicon', 'storage/' . $path);
        }

        return back()->with('success', 'General settings updated successfully!');
    }

    public function credit()
    {
        $settings = [];
        foreach (CustomerCreditService::defaults() as $key => $default) {
            $settings[$key] = CustomerCreditService::getSetting($key);
        }

        return view('admin.settings.credit', compact('settings'));
    }

    public function updateCredit(Request $request)
    {
        $validated = $request->validate([
            'credit_hold_grace_days' => 'required|integer|min:1',
            'enforce_credit_block' => 'nullable|boolean',
            'enforce_credit_limit' => 'nullable|boolean',
        ]);

        CustomerCreditService::setSetting('commission.credit_hold_grace_days', $validated['credit_hold_grace_days']);
        CustomerCreditService::setSetting('commission.enforce_credit_block', $request->boolean('enforce_credit_block'));
        CustomerCreditService::setSetting('commission.enforce_credit_limit', $request->boolean('enforce_credit_limit'));

        return back()->with('success', 'Credit settings updated successfully!');
    }
}

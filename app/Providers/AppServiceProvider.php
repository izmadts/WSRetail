<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // A freshly-unzipped/cloned deploy can reach the very first request
        // in one of two under-prepared states: no .env at all (deploy.sh's
        // bootstrap step was skipped, or hasn't run yet), or a .env freshly
        // copied from .env.example with APP_KEY still blank (that's only
        // ever filled in by the install wizard completing - see
        // InstallController::writeEnv() - which hasn't happened yet).
        // Either way, EVERY request - not just /install/* ones - needs a
        // non-empty APP_KEY just to get through Laravel's cookie-encryption
        // middleware, before the "not installed yet, redirect to /install"
        // logic even gets a chance to run. Confirmed live: visiting a truly
        // fresh checkout with no .env 500s with MissingAppKeyException
        // before the redirect can happen. Self-healed here, in register()
        // (runs during bootstrap, before the request pipeline executes),
        // by bootstrapping .env from the template and writing a real
        // (temporary) key if one isn't set yet - written to the file, not
        // just this request's config, so it's stable across the several
        // requests the install wizard itself needs. It gets fully replaced
        // by a permanent one the moment the wizard completes.
        if (!File::exists(base_path('.env')) && File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
        }

        if (empty(config('app.key')) && File::exists(base_path('.env'))) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $env = File::get(base_path('.env'));
            $env = preg_match('/^APP_KEY=.*/m', $env)
                ? preg_replace('/^APP_KEY=.*/m', "APP_KEY={$key}", $env)
                : $env . "\nAPP_KEY={$key}";
            File::put(base_path('.env'), $env);
            config(['app.key' => $key]);
        }

        // Before the install wizard finishes, there's no migrated database
        // to back SESSION_DRIVER/CACHE_STORE='database' (this app's
        // default) - the very first request would fail trying to read/
        // write a `sessions` table that doesn't exist yet. Originally
        // scoped to request()->is('install*'), but that missed the ONE
        // route a truly fresh visitor hits first: '/' itself, which must
        // redirect into /install - and StartSession runs as part of the
        // global 'web' middleware group on every route, including that
        // one, before its redirect logic ever gets a chance to execute.
        // Confirmed live: a fresh checkout 500s on '/' with "Base table
        // sessions doesn't exist" otherwise. The real condition that
        // matters is "has this app finished installing" (storage/installed
        // exists), not "is this URL under /install" - checked here, in
        // register() (runs during bootstrap, before the request pipeline -
        // including StartSession - executes), not in route middleware,
        // which would run too late to prevent that first failure.
        if (!File::exists(storage_path('installed'))) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
                'queue.default' => 'sync',
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for shared hosting MySQL key length limit
        Schema::defaultStringLength(191);

        // Settings > General's Timezone field was saved and displayed back
        // correctly but never actually applied anywhere - config('app.timezone')
        // stayed whatever config/app.php set at boot (LoadConfiguration runs
        // date_default_timezone_set() from THAT value, before the database
        // - and this setting - even exist yet), so every now()/Carbon call
        // and every date column silently used that instead, regardless of
        // what an admin picked here. Re-applying it now, after the database
        // is available, makes the setting the actual source of truth for
        // the rest of this request onward. Guarded on "installed" so a
        // fresh checkout with no settings table yet doesn't 500 on every
        // request before the install wizard has even run.
        if (File::exists(storage_path('installed'))) {
            $timezone = Setting::get('timezone');
            if ($timezone && $timezone !== config('app.timezone')) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        }

        // Settings > General's logo/app name were only ever passed to the
        // settings page itself (SettingsController::general()) - every
        // other view hardcoded "WSERP" as plain text and never showed any
        // logo, so uploading one there had no visible effect anywhere else.
        // Scoped to specific views (not '*') since a wildcard composer
        // would otherwise run this query again for every partial/component
        // include on the page, not just once per request.
        View::composer(
            ['layouts.admin', 'layouts.agent', 'layouts.guest', 'auth.login', 'auth.register'],
            function ($view) {
                $settings = Setting::whereIn('key', ['app_name', 'logo', 'favicon', 'theme_color', 'dark_mode_enabled'])
                    ->pluck('value', 'key');
                $view->with('siteName', $settings['app_name'] ?? config('app.name'));
                $view->with('siteLogo', $settings['logo'] ?? null);
                $view->with('siteFavicon', $settings['favicon'] ?? null);
                $view->with('themeColor', $settings['theme_color'] ?? config('themes.default'));
                // Setting::set() stores everything as a raw string - booleans
                // round-trip as the literal strings "1"/"" rather than PHP
                // true/false, so this can't just be a truthiness check on
                // the string (empty string IS falsy, but "0" is truthy).
                $view->with('darkModeEnabled', ($settings['dark_mode_enabled'] ?? '1') === '1');
            }
        );
    }
}

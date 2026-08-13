<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Setting;
use App\Services\LicenseService;
use Database\Seeders\AccountSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ExpenseCategorySeeder;
use Database\Seeders\IncomeCategorySeeder;
use Database\Seeders\CustomerGroupSeeder;
use Database\Seeders\CustomerCreditSettingsSeeder;
use Database\Seeders\RolePermissionSeeder;

/**
 * Web setup wizard (index -> step1 "database" -> step2 "admin + company" ->
 * complete), gated by the `not.installed` middleware (see
 * app/Http/Middleware/EnsureNotInstalled.php) which locks every route here
 * the moment storage/installed exists.
 *
 * Deliberately does NOT run the full DatabaseSeeder - that seeder creates
 * demo staff users, fake suppliers, and fake customers (see
 * database/seeders/DatabaseSeeder.php), which has no place on a real,
 * live install. Only structural defaults (chart of accounts, product/expense/
 * income categories, customer groups, and customer credit policy defaults,
 * role permissions) are seeded here - the one real account created is the
 * admin from this form, never a seeded placeholder.
 */
class InstallController extends Controller
{
    private function installedFlagPath(): string
    {
        return storage_path('installed');
    }

    public function index()
    {
        return view('install.index');
    }

    public function step1()
    {
        return view('install.step1');
    }

    public function postStep1(Request $request)
    {
        $validated = $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // Test against a throwaway connection name - doesn't touch the live
        // 'mysql' connection this request already booted with, so a failed
        // attempt here can't leave anything in a half-changed state.
        config(['database.connections.install_test' => [
            'driver' => 'mysql',
            'host' => $validated['db_host'],
            'port' => $validated['db_port'],
            'database' => $validated['db_database'],
            'username' => $validated['db_username'],
            'password' => $validated['db_password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            DB::connection('install_test')->getPdo();
        } catch (\Throwable $e) {
            DB::purge('install_test');
            return back()->withInput()->with('error', 'Could not connect to that database: ' . $e->getMessage());
        }
        DB::purge('install_test');

        session(['install.db' => $validated]);

        return redirect()->route('install.step2');
    }

    public function step2()
    {
        if (!session()->has('install.db')) {
            return redirect()->route('install.step1')->with('error', 'Please complete database setup first.');
        }

        return view('install.step2');
    }

    public function postStep2(Request $request)
    {
        // Migrating ~55 tables + seeding is a legitimate long-running,
        // one-time admin operation - it must not be bound by the request
        // timeout meant for ordinary pages. Confirmed via live testing that
        // this genuinely matters, not just theoretical: on one Windows/XAMPP
        // setup, each migration alone took 1-3s (antivirus real-time-
        // scanning MySQL's data directory is a common cause), 86s total for
        // just the schema - comfortably enough to blow through PHP's
        // default 120s max_execution_time before seeding/admin-creation
        // even started, which look identical to the .env-watcher problem
        // from the browser's side (connection never returns) but is a
        // completely separate cause with a completely separate fix.
        set_time_limit(0);

        $dbConfig = session('install.db');
        if (!$dbConfig) {
            return redirect()->route('install.step1')->with('error', 'Please complete database setup first.');
        }

        $validated = $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'license_key' => 'required|string|max:255',
        ]);

        // Point the REAL 'mysql' connection at the submitted database for
        // the rest of this request (writing .env alone wouldn't affect a
        // request already in flight - config is loaded once at boot).
        $this->applyDbConnection($dbConfig);

        // writeEnv() is deliberately NOT called here, before the real work -
        // it used to be, and that broke real installs under `php artisan
        // serve`: that command polls .env's mtime every 500ms (regardless of
        // any request in flight, see Illuminate\Foundation\Console\
        // ServeCommand::handle()) and kills+restarts its underlying `php -S`
        // worker the instant it changes, cutting off whatever request
        // happened to be running - which meant migrate/seed/admin-creation
        // below could get killed mid-way, sometimes before the admin
        // account was even created. Moved to the very end (see below) so
        // every real step here has already committed to the database before
        // .env is touched - if the .env-triggered restart still cuts off
        // the final redirect, storage/installed is already written and
        // EnsureNotInstalled will send a reload straight to the completion
        // page instead of failing.
        Artisan::call('migrate', ['--force' => true]);

        // License is checked right after migrate (the `license` table now
        // exists) and before any seeding/admin-account creation - a bad key
        // aborts here with nothing else committed except the (idempotent,
        // harmless) schema. The wizard can just be resubmitted with a
        // correct key.
        $licenseResult = app(LicenseService::class)->activate($validated['license_key']);
        if (!$licenseResult['ok']) {
            return back()->withInput()->with('error', 'License activation failed: ' . $licenseResult['message']);
        }

        foreach ([
            AccountSeeder::class,
            CategorySeeder::class,
            ExpenseCategorySeeder::class,
            IncomeCategorySeeder::class,
            CustomerGroupSeeder::class,
            CustomerCreditSettingsSeeder::class,
            RolePermissionSeeder::class,
        ] as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        if (!File::exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        $admin = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'is_active' => true,
            'approved_at' => now(),
        ]);

        Setting::set('app_name', $validated['company_name']);
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('uploads/settings', 'public');
            Setting::set('logo', 'storage/' . $path);
        }
        if (!empty($validated['company_phone'])) {
            Setting::set('company_phone', $validated['company_phone']);
        }
        if (!empty($validated['company_address'])) {
            Setting::set('company_address', $validated['company_address']);
        }

        File::put($this->installedFlagPath(), now()->toDateTimeString() . " by {$admin->email}\n");

        session()->forget('install.db');

        // Persisted to .env last, deliberately - see the comment above
        // applyDbConnection() for why. APP_URL is auto-detected from how
        // this wizard was reached, not typed in - one fewer thing to get
        // wrong.
        $this->writeEnv($dbConfig, $request->getSchemeAndHttpHost(), $validated['company_name']);

        return redirect()->route('install.complete');
    }

    public function complete()
    {
        if (!File::exists($this->installedFlagPath())) {
            return redirect()->route('install.index');
        }

        return view('install.complete');
    }

    private function applyDbConnection(array $dbConfig): void
    {
        config([
            'database.connections.mysql.host' => $dbConfig['db_host'],
            'database.connections.mysql.port' => $dbConfig['db_port'],
            'database.connections.mysql.database' => $dbConfig['db_database'],
            'database.connections.mysql.username' => $dbConfig['db_username'],
            'database.connections.mysql.password' => $dbConfig['db_password'] ?? '',
        ]);
        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
    }

    private function writeEnv(array $dbConfig, string $appUrl, string $appName): void
    {
        // Always built fresh from .env.example, never from whatever .env
        // already happens to be sitting there. An install should produce a
        // known, deterministic file from the template + what was collected
        // in this wizard - not patch someone's pre-existing environment.
        // (Confirmed the hard way: testing this locally against an already-
        // configured dev .env silently rewrote it instead of generating a
        // clean one.) On a genuine live-server deploy there's nothing to
        // collide with anyway - deploy.sh only creates .env from
        // .env.example if one doesn't already exist, so this always starts
        // from the same clean template a fresh clone would.
        $env = File::get(base_path('.env.example'));

        $replacements = [
            'APP_NAME' => $appName,
            // Generated directly here rather than via `Artisan::call('key:generate')`
            // - that command builds its search/replace regex from the
            // currently-configured config('app.key') (loaded once at this
            // request's boot), not from the file content this method just
            // wrote. Since that in-memory value is almost always stale by
            // now (this app already had a real key before this install
            // request began), its regex looks for the OLD key and finds no
            // match against the fresh blank APP_KEY= line - silently
            // writing nothing. Confirmed live: it printed "Application key
            // set successfully" while leaving APP_KEY genuinely blank.
            // Generating it inline here, the same way every other value in
            // this array is handled, sidesteps that entirely.
            'APP_KEY' => 'base64:' . base64_encode(\Illuminate\Encryption\Encrypter::generateKey(config('app.cipher'))),
            'APP_URL' => $appUrl,
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $dbConfig['db_host'],
            'DB_PORT' => (string) $dbConfig['db_port'],
            'DB_DATABASE' => $dbConfig['db_database'],
            'DB_USERNAME' => $dbConfig['db_username'],
            'DB_PASSWORD' => $dbConfig['db_password'] ?? '',
            // Shared secret the customer/storefront API requires on every
            // request to /connect (VerifyIntegrationKey middleware).
            // .env.example ships it blank, which fails closed rather than
            // open, but that leaves the whole customer API silently dead
            // until someone notices and sets it by hand. Generating a real
            // one here means it works immediately - whoever wires up the
            // storefront just copies this value from the live .env.
            'CUSTOMER_API_INTEGRATION_KEY' => Str::random(64),
        ];

        foreach ($replacements as $key => $value) {
            $escaped = str_contains($value, ' ') ? '"' . $value . '"' : $value;
            $pattern = "/^{$key}=.*/m";
            $env = preg_match($pattern, $env)
                ? preg_replace($pattern, "{$key}={$escaped}", $env)
                : $env . "\n{$key}={$escaped}";
        }

        File::put(base_path('.env'), $env);

        // Reflected in this request's own runtime config too, in case
        // anything reads it before the process ends (the redirect response
        // is about to be built and sent right after this method returns).
        config(['app.key' => $replacements['APP_KEY']]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\Integration;
use App\Models\Setting;
use App\Models\UrlRedirect;
use App\Services\IntegrationImportService;
use App\Services\WooCommerceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Settings > Integrations. v1 ships one working connector (WooCommerce,
 * one-time product/customer/order import) plus placeholder cards for
 * platforms not built yet, so the hub's shape doesn't have to change when
 * they are. Whole controller sits behind role:admin (see routes/web.php
 * settings group), same as License management.
 */
class IntegrationController extends Controller
{
    public function index()
    {
        $woocommerce = Integration::firstOrCreate(['platform' => 'woocommerce'], ['status' => 'disconnected']);

        $recentBatches = $woocommerce->exists
            ? ImportBatch::where('integration_id', $woocommerce->id)->latest()->limit(10)->get()
            : collect();

        $redirectCount = UrlRedirect::count();

        return view('admin.integrations.index', compact('woocommerce', 'recentBatches', 'redirectCount'));
    }

    public function connectWooCommerce(Request $request)
    {
        $validated = $request->validate([
            'site_url' => 'required|url|max:255',
            'consumer_key' => 'required|string|min:10|max:255',
            'consumer_secret' => 'required|string|min:10|max:255',
        ]);

        $client = new WooCommerceClient($validated['site_url'], $validated['consumer_key'], $validated['consumer_secret']);
        $result = $client->testConnection();

        $integration = Integration::firstOrCreate(['platform' => 'woocommerce']);

        if (!$result['ok']) {
            $integration->update([
                'status' => 'error',
                'last_error_at' => now(),
                'last_error' => $result['message'],
            ]);

            return back()->with('error', 'Could not connect: ' . $result['message']);
        }

        $integration->update([
            'status' => 'connected',
            'credentials' => [
                'site_url' => rtrim($validated['site_url'], '/'),
                'consumer_key' => $validated['consumer_key'],
                'consumer_secret' => $validated['consumer_secret'],
            ],
            'connected_at' => now(),
            'last_error' => null,
            'last_error_at' => null,
        ]);

        return back()->with('success', 'Connected to WooCommerce successfully.');
    }

    public function disconnectWooCommerce()
    {
        Integration::where('platform', 'woocommerce')->update([
            'status' => 'disconnected',
            'credentials' => null,
            'connected_at' => null,
        ]);

        return back()->with('success', 'WooCommerce disconnected.');
    }

    public function stageImport(Request $request, string $type, IntegrationImportService $service)
    {
        abort_unless(in_array($type, ['products', 'customers', 'orders', 'settings'], true), 404);

        $integration = Integration::where('platform', 'woocommerce')->firstOrFail();
        abort_unless($integration->isConnected(), 422, 'WooCommerce is not connected.');

        $batch = match ($type) {
            'products' => $service->stageProducts($integration, Auth::id()),
            'customers' => $service->stageCustomers($integration, Auth::id()),
            'orders' => $service->stageOrders($integration, Auth::id()),
            'settings' => $service->stageSettings($integration, Auth::id()),
        };

        if ($batch->fetched_count === 0) {
            return redirect()->route('admin.integrations.index')
                ->with('error', 'Nothing to import - WooCommerce returned zero ' . $type . ', or the connection failed. Check the site URL/keys and try again.');
        }

        return redirect()->route('admin.integrations.imports.review', $batch);
    }

    public function reviewImport(ImportBatch $batch)
    {
        abort_if($batch->status !== 'staged', 404);

        // Deliberately not paginated: the commit form below submits
        // checkboxes for every item shown on THIS page only (HTML forms
        // don't carry state across page loads), so paginating here would
        // silently treat every item on a page the admin never visited as
        // excluded. Fine for a v1 one-time import - a very large catalog
        // just makes for a long scroll, not a data-loss bug.
        $items = $batch->items()->orderBy('id')->get();

        return view('admin.integrations.review', compact('batch', 'items'));
    }

    public function commitImport(Request $request, ImportBatch $batch, IntegrationImportService $service)
    {
        abort_if($batch->status !== 'staged', 404);

        $includedIds = $request->input('included', []);

        // Every item not explicitly checked on the review screen is
        // excluded from the commit - an admin unchecking a row is real
        // intent, not an accident to second-guess.
        $batch->items()->update(['included' => false]);
        if (!empty($includedIds)) {
            $batch->items()->whereIn('id', $includedIds)->where('action', '!=', 'skip')->update(['included' => true]);
        }

        $service->commit($batch->fresh('items'));

        // $batch->fresh('items') above returns a SEPARATE cloned instance -
        // commit() writes the real counts onto that clone, not onto this
        // $batch variable, which would otherwise still hold its
        // just-created defaults (0/0/0) for the message below. refresh()
        // re-syncs this object from the same DB row the clone just wrote.
        $batch->refresh();

        return redirect()->route('admin.integrations.index')->with(
            'success',
            "Import complete: {$batch->created_count} created, {$batch->updated_count} updated, {$batch->skipped_count} skipped."
        );
    }

    public function cancelImport(ImportBatch $batch)
    {
        abort_if($batch->status !== 'staged', 404);

        $batch->update(['status' => 'cancelled']);

        return redirect()->route('admin.integrations.index')->with('success', 'Import cancelled - nothing was changed.');
    }

    /**
     * SEO redirects captured during product imports (old store URL -> new
     * WSRetail product page). See IntegrationImportService::recordRedirect().
     */
    public function redirects()
    {
        $redirects = UrlRedirect::with('product')->latest()->paginate(50);
        $storefrontUrl = rtrim(Setting::get('storefront_url', ''), '/');

        return view('admin.integrations.redirects', compact('redirects', 'storefrontUrl'));
    }

    /**
     * A plain redirect-lookup API only helps if the customer routes their
     * OLD domain's traffic through the new Next.js storefront. Many won't -
     * they'll point the old web server (or its CDN) straight at the new
     * domain via a server-level 301. This gives them a ready-to-paste rule
     * file for that case, so nothing has to be typed out by hand.
     */
    public function exportRedirects(string $format)
    {
        abort_unless(in_array($format, ['htaccess', 'nginx'], true), 404);

        $redirects = UrlRedirect::orderBy('old_path')->get();
        $storefrontUrl = rtrim(Setting::get('storefront_url', ''), '/');

        if ($format === 'htaccess') {
            $lines = $redirects->map(fn ($r) => "Redirect 301 {$r->old_path} {$storefrontUrl}{$r->new_path}");
            $content = "# Generated by WSRetail - paste into your old store's .htaccess\n" . $lines->implode("\n") . "\n";
            $filename = 'wsretail-redirects.htaccess';
        } else {
            $lines = $redirects->map(fn ($r) => "    rewrite ^{$r->old_path}$ {$storefrontUrl}{$r->new_path} permanent;");
            $content = "# Generated by WSRetail - paste inside your old store's nginx server block\n" . $lines->implode("\n") . "\n";
            $filename = 'wsretail-redirects.nginx.conf';
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

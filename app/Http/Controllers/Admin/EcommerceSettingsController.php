<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Settings > Ecommerce - storefront-facing settings that don't belong in
 * the admin's own General settings (app name/currency/theme) or in a
 * per-product/per-order field: Store details, Payment methods offered at
 * checkout, and Shipping. Mirrors the shape of WooCommerce's own Settings
 * tabs so it's a familiar layout for a store owner migrating from there.
 *
 * v1 scope, deliberately: Payment is method TOGGLES only (no gateway/API
 * integration - WSRetail has no online payment processor wired up yet, see
 * CHANGELOG). Shipping is a single flat rate + free-shipping threshold, not
 * per-zone/per-city rates - a real multi-zone shipping engine is a much
 * larger feature, not a settings-page addition.
 */
class EcommerceSettingsController extends Controller
{
    public function store()
    {
        $settings = [
            'storefront_url' => Setting::get('storefront_url', ''),
            'store_address' => Setting::get('store_address', ''),
            'store_phone' => Setting::get('store_phone', ''),
            'store_email' => Setting::get('store_email', ''),
        ];

        return view('admin.ecommerce.store', compact('settings'));
    }

    public function updateStore(Request $request)
    {
        $validated = $request->validate([
            'storefront_url' => 'nullable|url|max:255',
            'store_address' => 'nullable|string|max:500',
            // Digits/spaces/+/-/()/. only - loose enough for any country's
            // format, tight enough to catch someone fat-fingering letters
            // into a phone field.
            'store_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]*$/'],
            'store_email' => 'nullable|email|max:255',
        ]);

        Setting::set('storefront_url', rtrim($validated['storefront_url'] ?? '', '/'));
        Setting::set('store_address', $validated['store_address'] ?? '');
        Setting::set('store_phone', $validated['store_phone'] ?? '');
        Setting::set('store_email', $validated['store_email'] ?? '');

        return back()->with('success', 'Store settings saved.');
    }

    public function payment()
    {
        $methods = PaymentMethod::ordered()->get();

        return view('admin.ecommerce.payment', compact('methods'));
    }

    public function storePaymentMethod(Request $request)
    {
        // Deliberately separate field names from updatePaymentMethod()'s
        // ("new_name"/"new_description" vs "name"/"description") - this
        // form shares a page with one edit form per existing row, and a
        // shared field name would mean a failed add-form validation flashes
        // old('name') = '' globally, blanking every row's displayed name.
        $validated = $request->validate([
            'new_name' => 'required|string|max:100|min:2',
            'new_description' => 'nullable|string|max:1000',
        ]);

        $code = \Illuminate\Support\Str::slug($validated['new_name'], '_');
        if (PaymentMethod::where('code', $code)->exists()) {
            $code .= '_' . uniqid();
        }

        PaymentMethod::create([
            'code' => $code,
            'name' => $validated['new_name'],
            'description' => $validated['new_description'] ?? null,
            'is_enabled' => true,
            'sort_order' => (PaymentMethod::max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('success', 'Payment method added.');
    }

    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|min:2',
            'description' => 'nullable|string|max:1000',
        ]);

        $paymentMethod->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return back()->with('success', 'Payment method updated.');
    }

    public function destroyPaymentMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return back()->with('success', 'Payment method removed.');
    }

    public function shipping()
    {
        $settings = [
            'shipping_flat_rate' => Setting::get('shipping_flat_rate', '0'),
            'shipping_free_threshold' => Setting::get('shipping_free_threshold', ''),
        ];

        return view('admin.ecommerce.shipping', compact('settings'));
    }

    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'shipping_flat_rate' => 'required|numeric|min:0|max:100000',
            'shipping_free_threshold' => 'nullable|numeric|min:0|max:10000000',
        ]);

        Setting::set('shipping_flat_rate', $validated['shipping_flat_rate']);
        Setting::set('shipping_free_threshold', $validated['shipping_free_threshold'] ?? '');

        return back()->with('success', 'Shipping settings saved.');
    }
}

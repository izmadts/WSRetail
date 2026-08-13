<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LicensePurchaseRequested;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Reachable regardless of lock state (see EnsureLicensed) so a locked-out
 * admin always has a way to fix it. Mutating actions are further gated to
 * the admin role here at the controller level - everyone can see status,
 * only admin can activate/deactivate/recheck.
 */
class LicenseController extends Controller
{
    public function __construct(private LicenseService $licenseService)
    {
    }

    public function index()
    {
        $license = License::current();

        return view('admin.license.index', [
            'license' => $license,
            'locked' => $this->licenseService->isLocked(),
            'lockReason' => $this->licenseService->lockReason(),
            'canManage' => Auth::user()->role === 'admin',
        ]);
    }

    public function activate(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'license_key' => 'required|string|max:255',
        ]);

        $result = $this->licenseService->activate($validated['license_key']);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function recheck()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $result = $this->licenseService->validate();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function deactivate()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $this->licenseService->deactivate();

        return back()->with('success', 'License deactivated on this installation. You can activate a different key now.');
    }

    /**
     * "Buy a license" form on the locked/unactivated license page. No local
     * DB record is kept - this only emails the owner (with the payment slip
     * attached straight from the upload, never written to permanent
     * storage) so a fresh, unlicensed install has nothing sensitive sitting
     * on someone else's server.
     */
    public function purchaseRequest(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'domain' => 'nullable|string|max:255',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'payment_slip' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $slip = $request->file('payment_slip');

        try {
            Mail::to(config('services.license_purchase.notify_email'))->send(
                new LicensePurchaseRequested([
                    'name' => $validated['name'],
                    'business_name' => $validated['business_name'] ?? null,
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'domain' => $validated['domain'] ?? null,
                    'amount_paid' => $validated['amount_paid'],
                    'notes' => $validated['notes'] ?? null,
                    'has_slip' => (bool) $slip,
                ], $slip)
            );
        } catch (\Throwable $e) {
            Log::error('License purchase request email failed: ' . $e->getMessage());

            return back()->with('error', 'Could not send your request automatically. Please reach out directly via WhatsApp or email instead - see below.');
        }

        return back()->with('success', 'Request sent! We\'ll be in touch on ' . $validated['phone'] . ' (or your email) shortly after confirming the payment.');
    }
}

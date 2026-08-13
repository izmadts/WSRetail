<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Demo Mode only (Settings > General) - shows the Urdu intro/pitch
        // popup once, right after login, on whichever page the user lands
        // on. A flash value rather than a Setting/DB flag: it must reset on
        // every fresh login, not just once ever per install.
        if (Setting::get('demo_mode', '0') === '1') {
            $request->session()->flash('show_demo_intro', true);
        }

        // Redirect based on role
        $user = Auth::user();

        if ($user->isPosManager()) {
            return redirect()->route('admin.sales.pos');
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

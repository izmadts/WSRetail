<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $agent = Auth::user();
        return view('agent.profile.index', compact('agent'));
    }

    public function update(Request $request)
    {
        $agent = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($agent->id)],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'whatsapp_number' => 'nullable|string|max:20',
            'payout_account_type' => 'nullable|string',
            'payout_account_title' => 'nullable|string',
            'payout_account_number' => 'nullable|string',
            'payout_account_provider' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $agent->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }
}
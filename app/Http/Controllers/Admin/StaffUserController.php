<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * CRUD for admin/manager/accountant staff accounts. Sales agents have
 * their own richer management flow (approval, commission fields, KYC
 * documents) in AgentManagementController - this is deliberately not that,
 * it's the "there was no way to create a manager/accountant user at all"
 * gap the settings page needed to close.
 */
class StaffUserController extends Controller
{
    private const STAFF_ROLES = ['admin', 'manager', 'accountant'];

    public function index()
    {
        $users = User::whereIn('role', self::STAFF_ROLES)->orderBy('name')->get();
        return view('admin.settings.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.settings.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('admin.settings.users.index')
            ->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        if (!in_array($user->role, self::STAFF_ROLES)) {
            abort(404);
        }

        return view('admin.settings.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!in_array($user->role, self::STAFF_ROLES)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }

        // An admin can't lock themselves out.
        if ($user->id === Auth::id() && (!$validated['is_active'] || $validated['role'] !== 'admin')) {
            return back()->with('error', 'You cannot deactivate or demote your own account.');
        }

        $user->update($validated);

        return redirect()->route('admin.settings.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if (!in_array($user->role, self::STAFF_ROLES)) {
            abort(404);
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.settings.users.index')
            ->with('success', 'User deleted successfully!');
    }
}

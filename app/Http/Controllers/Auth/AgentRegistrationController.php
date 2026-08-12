<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AgentRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        if (Setting::get('registration_enabled', '1') !== '1') {
            return view('auth.agent-register-closed');
        }

        return view('auth.agent-register');
    }

    public function register(Request $request)
    {
        // Re-checked here too (not just on the GET form) - the form being
        // hidden doesn't stop a direct POST to this route.
        if (Setting::get('registration_enabled', '1') !== '1') {
            return redirect()->route('agent.register')->with('error', 'New sales agent registration is currently closed.');
        }

        $validator = Validator::make($request->all(), [
            // Personal Information
            'name' => 'required|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'cnic' => 'required|string|max:20|unique:users',

            // Documents
            'cnic_front_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'cnic_back_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',

            // Payout
            'payout_account_type' => 'required|string',
            'payout_account_title' => 'required|string',
            'payout_account_number' => 'required|string',
            'payout_account_provider' => 'required|string',

            // Reference
            'reference_name' => 'nullable|string|max:255',
            'reference_phone_number' => 'nullable|string|max:20',
            'reference_address' => 'nullable|string',

            // Password
            'password' => 'required|string|min:8|confirmed',
            'policy_accepted' => 'accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle file uploads
        $cnicFrontPath = $request->file('cnic_front_image')->store('agent_documents', 'public');
        $cnicBackPath = $request->file('cnic_back_image')->store('agent_documents', 'public');
        $personalPhotoPath = $request->file('personal_photo')->store('agent_documents', 'public');

        // Create user as sales agent (pending approval)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'sales_agent',  // Set role as sales_agent
            'is_active' => false,     // Inactive until approved by admin

            // Personal
            'phone' => $request->phone_number,
            'cnic' => $request->cnic,
            'address' => $request->address,
            'city' => $request->city,
            'guardian_name' => $request->guardian_name,
            'whatsapp_number' => $request->whatsapp_number,

            // Documents
            'cnic_front_image' => $cnicFrontPath,
            'cnic_back_image' => $cnicBackPath,
            'personal_photo' => $personalPhotoPath,

            // Payout
            'payout_account_type' => $request->payout_account_type,
            'payout_account_title' => $request->payout_account_title,
            'payout_account_number' => $request->payout_account_number,
            'payout_account_provider' => $request->payout_account_provider,

            // Reference in admin_note
            'admin_note' => $request->reference_name ?
                "Reference: {$request->reference_name}, Phone: {$request->reference_phone_number}, Address: {$request->reference_address}" :
                null,
        ]);

        return redirect()->route('agent.register.success')
            ->with('success', 'Your application has been submitted! You will be notified once approved.');
    }

    public function showSuccess()
    {
        return view('auth.agent-register-success');
    }
}

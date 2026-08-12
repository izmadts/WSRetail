<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\Setting;
use App\Models\User;
use App\Http\Resources\Api\AgentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends ApiController
{
    /**
     * Self-service sign-up for a new sales agent, mirroring
     * Auth\AgentRegistrationController's web flow (same fields, same
     * pending-approval outcome) but as JSON for the Flutter client. No
     * token is issued here - the account is created with is_active=false
     * and no approved_at, so it can't log in until an admin approves it
     * (Admin\AgentManagementController).
     */
    public function register(Request $request)
    {
        if (Setting::get('registration_enabled', '1') !== '1') {
            return $this->error('New sales agent registration is currently closed. Please contact the admin.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'cnic' => 'required|string|max:20|unique:users',

            'cnic_front_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'cnic_back_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'personal_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',

            'payout_account_type' => 'required|string',
            'payout_account_title' => 'required|string',
            'payout_account_number' => 'required|string',
            'payout_account_provider' => 'required|string',

            'reference_name' => 'nullable|string|max:255',
            'reference_phone_number' => 'nullable|string|max:20',
            'reference_address' => 'nullable|string',

            'password' => 'required|string|min:8|confirmed',
            'policy_accepted' => 'accepted',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $cnicFrontPath = $request->file('cnic_front_image')->store('agent_documents', 'public');
        $cnicBackPath = $request->file('cnic_back_image')->store('agent_documents', 'public');
        $personalPhotoPath = $request->file('personal_photo')->store('agent_documents', 'public');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'sales_agent',
            'is_active' => false,

            'phone' => $request->phone_number,
            'cnic' => $request->cnic,
            'address' => $request->address,
            'city' => $request->city,
            'guardian_name' => $request->guardian_name,
            'whatsapp_number' => $request->whatsapp_number,

            'cnic_front_image' => $cnicFrontPath,
            'cnic_back_image' => $cnicBackPath,
            'personal_photo' => $personalPhotoPath,

            'payout_account_type' => $request->payout_account_type,
            'payout_account_title' => $request->payout_account_title,
            'payout_account_number' => $request->payout_account_number,
            'payout_account_provider' => $request->payout_account_provider,

            'admin_note' => $request->reference_name
                ? "Reference: {$request->reference_name}, Phone: {$request->reference_phone_number}, Address: {$request->reference_address}"
                : null,
        ]);

        return $this->success(
            new AgentResource($user),
            'Your application has been submitted! You will be notified once approved.',
            201
        );
    }

    /**
     * Issues a Sanctum personal access token. Deliberately NOT using
     * Auth::attempt() (that's session-based) - a mobile client is
     * stateless, so credentials are checked directly and a token handed
     * back instead. Same active/approved/role gate as the web LoginRequest.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid email or password.', 401);
        }

        if ($user->role !== 'sales_agent') {
            return $this->error('This login is for sales agent accounts only.', 403);
        }

        if (!$user->is_active) {
            return $this->error(
                is_null($user->approved_at)
                    ? 'Your agent application is still pending admin approval.'
                    : 'Your account has been deactivated. Please contact the admin.',
                403
            );
        }

        if (is_null($user->approved_at)) {
            return $this->error('Your agent application is still pending admin approval.', 403);
        }

        $deviceName = $request->input('device_name', 'flutter-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->update(['last_login_at' => now()]);

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'agent' => new AgentResource($user),
        ], 'Logged in successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        return $this->success(new AgentResource($request->user()));
    }

    public function updateProfile(Request $request)
    {
        $agent = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($agent->id)],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'whatsapp_number' => 'nullable|string|max:20',
            'payout_account_type' => 'nullable|string|in:bank,easypaisa,jazzcash',
            'payout_account_title' => 'nullable|string',
            'payout_account_number' => 'nullable|string',
            'payout_account_provider' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $agent->update($validator->validated());

        return $this->success(new AgentResource($agent->fresh()), 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $agent = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        if (!Hash::check($request->current_password, $agent->password)) {
            return $this->error('Current password is incorrect.', 422, ['current_password' => ['Current password is incorrect.']]);
        }

        $agent->update(['password' => Hash::make($request->password)]);

        // Revoke every other token so a stolen/old session can't keep
        // using the account after the agent deliberately changes their
        // password - the token used to make THIS request stays valid.
        $agent->tokens()->where('id', '!=', $agent->currentAccessToken()->id)->delete();

        return $this->success(null, 'Password changed successfully.');
    }
}

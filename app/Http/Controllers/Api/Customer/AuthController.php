<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Http\Resources\Api\Customer\CustomerProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    /**
     * One-time entry point for the storefront: no password, no OTP screen -
     * the storefront's backend already owns identity/verification on its
     * side (it authenticates this call with the shared integration key).
     * This either creates a new Customer or matches an existing one BY
     * PHONE and hands back a Sanctum token the storefront stores and reuses
     * for every later call. Protected by the integration.key middleware,
     * not by anything customer-specific - that's the actual trust boundary
     * here, since phone number alone is not a secret.
     *
     * Every new storefront customer defaults to the Retail customer_group.
     * See OrderController::resolveChannel, which prices off customer_group.
     */
    public function connect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'gps_location' => 'nullable|string|max:255',
            'cnic' => 'nullable|string|max:20',
            'ntn' => 'nullable|string|max:20',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();

        $customer = Customer::where('phone', $data['phone'])->first();

        $profileFields = array_filter([
            'name' => $data['name'] ?? null,
            'shop_name' => $data['business_name'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'gps_location' => $data['gps_location'] ?? null,
            'cnic' => $data['cnic'] ?? null,
            'ntn' => $data['ntn'] ?? null,
        ], fn ($v) => $v !== null);

        if ($customer) {
            $customer->fill($profileFields);
            // customer_group_id is never touched on reconnect - once an
            // admin has set a pricing tier it isn't silently reset just
            // because the storefront called /connect again.
            $customer->is_active = true;
            $customer->save();
        } else {
            $retailGroupId = CustomerGroup::where('price_field', 'sale_price')->value('id');

            $customer = Customer::create(array_merge($profileFields, [
                'phone' => $data['phone'],
                'customer_group_id' => $retailGroupId,
                'is_active' => true,
            ]));
        }

        $token = $customer->createToken($data['device_name'] ?? 'storefront')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'customer' => new CustomerProfileResource($customer->fresh('customerGroup')),
        ], 'Connected successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        return $this->success(new CustomerProfileResource($this->customer()->load('customerGroup')));
    }

    /**
     * Deliberately excludes phone - that's the /connect matching key, so
     * changing it here would let a customer silently detach themselves from
     * their own token history. A phone change goes through an admin.
     */
    public function updateProfile(Request $request)
    {
        $customer = $this->customer();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'gps_location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();
        if (array_key_exists('business_name', $validated)) {
            $validated['shop_name'] = $validated['business_name'];
            unset($validated['business_name']);
        }

        $customer->update($validated);

        return $this->success(new CustomerProfileResource($customer->fresh('customerGroup')), 'Profile updated successfully.');
    }
}

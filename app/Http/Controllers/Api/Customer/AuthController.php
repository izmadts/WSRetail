<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use App\Http\Resources\Api\Customer\CustomerProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    /**
     * One-time entry point for the seller app: no password, no OTP screen -
     * the seller app already owns identity/verification on its side. This
     * either creates a new Customer or matches an existing one BY PHONE
     * (so it also merges with a customer an agent already registered
     * manually in the web/agent app) and hands back a Sanctum token the
     * seller app stores and reuses for every later call. Protected by the
     * integration.key middleware, not by anything customer-specific -
     * that's the actual trust boundary here, since phone number alone is
     * not a secret.
     *
     * agent_id / direct decide who gets commission credit on this
     * customer's orders (created_by_agent_id) - NOT pricing. Every new
     * seller-app customer defaults to the Wholesale customer_group
     * regardless of agent, per policy: "all vendors/shop owners are
     * wholesale customers by default, admin or agent can change it."
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
            'agent_id' => 'nullable|integer',
            'direct' => 'nullable|boolean',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        $wantsDirect = (bool) ($data['direct'] ?? false);

        if (!empty($data['agent_id']) && !$wantsDirect) {
            // This whole API is a wholesale-only channel - the agent must be
            // allowed to work wholesale too (unrestricted/null channel counts
            // as allowed, same as everywhere else this is checked).
            $agentValid = User::where('id', $data['agent_id'])
                ->where('role', 'sales_agent')
                ->where('is_active', true)
                ->whereNotNull('approved_at')
                ->where(fn ($q) => $q->whereNull('channel')->orWhereIn('channel', ['wholesale', 'both']))
                ->exists();

            if (!$agentValid) {
                return $this->error('Selected sales agent is not valid or not active.', 422, [
                    'agent_id' => ['This agent does not exist or is not an active, approved sales agent.'],
                ]);
            }
        }

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

            if ($wantsDirect) {
                $customer->created_by_agent_id = null;
                $customer->is_agent_customer = false;
            } elseif (!empty($data['agent_id'])) {
                $customer->created_by_agent_id = $data['agent_id'];
                $customer->is_agent_customer = true;
            }
            // Neither given: leave the existing agent link untouched.
            // customer_group_id is likewise never touched on reconnect -
            // once an admin/agent has set a pricing tier it isn't silently
            // reset just because the seller app called /connect again.

            $customer->is_active = true;
            $customer->save();
        } else {
            $wholesaleGroupId = CustomerGroup::where('price_field', 'wholesale_price')->value('id');

            $customer = Customer::create(array_merge($profileFields, [
                'phone' => $data['phone'],
                'created_by_agent_id' => $wantsDirect ? null : ($data['agent_id'] ?? null),
                'is_agent_customer' => !$wantsDirect && !empty($data['agent_id']),
                'customer_group_id' => $wholesaleGroupId,
                'is_active' => true,
            ]));
        }

        $token = $customer->createToken($data['device_name'] ?? 'seller-app')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'customer' => new CustomerProfileResource($customer->fresh(['customerGroup', 'createdByAgent'])),
        ], 'Connected successfully.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        return $this->success(new CustomerProfileResource($this->customer()->load(['customerGroup', 'createdByAgent'])));
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

        return $this->success(new CustomerProfileResource($customer->fresh(['customerGroup', 'createdByAgent'])), 'Profile updated successfully.');
    }
}

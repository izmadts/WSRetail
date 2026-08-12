<?php

namespace App\Http\Resources\Api\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The customer's own view of themselves - richer than Api\Agent's
 * CustomerResource (which is what an AGENT needs to see about a customer
 * they manage), since this also carries the pricing channel "so the
 * customer can see their own details", per the original request this
 * API was built for.
 */
class CustomerProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'business_name' => $this->shop_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'gps_location' => $this->gps_location,
            'credit_limit' => (float) $this->credit_limit,
            'credit_days' => (int) $this->credit_days,
            'balance' => (float) $this->balance,
            'is_active' => (bool) $this->is_active,
            'order_count' => (int) $this->order_count,

            // The linked sales agent is purely relationship/commission
            // attribution - it does NOT decide pricing.
            'agent' => $this->whenLoaded('createdByAgent', fn () => $this->createdByAgent ? [
                'id' => $this->createdByAgent->id,
                'name' => $this->createdByAgent->name,
                'phone' => $this->createdByAgent->phone,
            ] : null),

            // customer_group is informational only here (still useful for
            // reporting/reconciliation elsewhere in WSERP) - it does NOT
            // drive Mandi pricing. This whole API is a wholesale-only
            // channel, unconditionally - see the note on
            // Api\Customer\OrderController::resolveChannel(). order_channel
            // is therefore always "wholesale", not derived from the group.
            'customer_group' => $this->whenLoaded('customerGroup', fn () => $this->customerGroup ? [
                'id' => $this->customerGroup->id,
                'name' => $this->customerGroup->name,
                'price_field' => $this->customerGroup->price_field,
            ] : null),
            'order_channel' => 'wholesale',

            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}

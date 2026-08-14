<?php

namespace App\Http\Resources\Api\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The customer's own view of themselves.
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
            'address_2' => $this->address_2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'gps_location' => $this->gps_location,
            'credit_limit' => (float) $this->credit_limit,
            'credit_days' => (int) $this->credit_days,
            'balance' => (float) $this->balance,
            'is_active' => (bool) $this->is_active,
            'order_count' => (int) $this->order_count,

            // customer_group is informational only here (still useful for
            // reporting/reconciliation elsewhere in WSRetail) - it does NOT
            // drive storefront pricing. This whole API is a retail-only
            // channel, unconditionally - see the note on
            // Api\Customer\OrderController::resolveChannel(). order_channel
            // is therefore always "retail", not derived from the group.
            'customer_group' => $this->whenLoaded('customerGroup', fn () => $this->customerGroup ? [
                'id' => $this->customerGroup->id,
                'name' => $this->customerGroup->name,
                'price_field' => $this->customerGroup->price_field,
            ] : null),
            'order_channel' => 'retail',

            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}

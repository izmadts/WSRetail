<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'cnic' => $this->cnic,
            'ntn' => $this->ntn,
            'opening_balance' => (float) $this->opening_balance,
            'credit_limit' => (float) $this->credit_limit,
            'credit_days' => (int) $this->credit_days,
            'balance' => (float) $this->balance,
            'notes' => $this->notes,
            'is_active' => (bool) $this->is_active,
            'order_count' => (int) $this->order_count,
            'customer_group' => $this->whenLoaded('customerGroup', fn () => $this->customerGroup ? [
                'id' => $this->customerGroup->id,
                'name' => $this->customerGroup->name,
                'price_field' => $this->customerGroup->price_field,
            ] : null),
            'created_at' => optional($this->created_at)->toIso8601String(),
            // Only populated on show() - index() doesn't load this relation.
            'recent_sales' => SaleResource::collection($this->whenLoaded('sales')),
        ];
    }
}

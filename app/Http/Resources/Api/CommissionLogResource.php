<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class CommissionLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'sale_invoice_no' => $this->whenLoaded('sale', fn () => $this->sale?->invoice_no),
            'reference_type' => $this->reference_type,
            'amount' => (float) $this->amount,
            'paid_amount' => (float) $this->paid_amount,
            'due_amount' => (float) $this->due_amount,
            'commission_rate' => (float) $this->commission_rate,
            'commission_type' => $this->commission_type,
            'description' => $this->description,
            'is_paid' => (bool) $this->is_paid,
            'paid_date' => optional($this->paid_date)->format('Y-m-d'),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}

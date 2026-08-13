<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'code' => $this->customer->code,
                'phone' => $this->customer->phone,
            ] : null),
            'sale_date' => optional($this->sale_date)->format('Y-m-d'),
            'payment_term' => $this->payment_term,
            'source' => $this->source,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'sub_total' => (float) $this->sub_total,
            'discount' => (float) $this->discount,
            'discount_type' => $this->discount_type,
            'tax' => (float) $this->tax,
            'shipping_cost' => (float) $this->shipping_cost,
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'due_amount' => (float) $this->due_amount,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'product_unit' => $item->product?->unit,
                'product_image' => $item->product?->image,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount' => (float) $item->discount,
                'tax' => (float) $item->tax,
                'total_price' => (float) $item->total_price,
            ])),
            'payments' => $this->whenLoaded('payments', fn () => $this->payments->map(fn ($p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'payment_date' => optional($p->payment_date)->format('Y-m-d'),
                'payment_method' => $p->payment_method,
                'reference_no' => $p->reference_no,
                'notes' => $p->notes,
            ])),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}

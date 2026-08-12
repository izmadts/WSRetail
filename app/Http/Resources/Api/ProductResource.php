<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'description' => $this->description,
            'unit' => $this->unit,
            'sale_price' => (float) $this->sale_price,
            'wholesale_price' => (float) $this->wholesale_price,
            'purchase_price' => (float) $this->purchase_price,
            'current_stock' => (float) $this->current_stock,
            'is_retail' => (bool) $this->is_retail,
            'is_wholesale' => (bool) $this->is_wholesale,
            'image' => $this->image,
        ];
    }
}

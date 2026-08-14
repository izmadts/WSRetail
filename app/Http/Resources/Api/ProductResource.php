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
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'description' => $this->description,
            'short_description' => $this->short_description,
            'brand' => $this->brand,
            'unit' => $this->unit,
            'sale_price' => (float) ($this->has_variants ? $this->variants->min('sale_price') : $this->sale_price),
            'wholesale_price' => (float) ($this->has_variants ? $this->variants->min('wholesale_price') : $this->wholesale_price),
            'purchase_price' => (float) $this->purchase_price,
            'current_stock' => (float) $this->totalStock(),
            'weight' => $this->weight !== null ? (float) $this->weight : null,
            'length' => $this->length !== null ? (float) $this->length : null,
            'width' => $this->width !== null ? (float) $this->width : null,
            'height' => $this->height !== null ? (float) $this->height : null,
            'is_retail' => (bool) $this->is_retail,
            'is_wholesale' => (bool) $this->is_wholesale,
            'has_variants' => (bool) $this->has_variants,
            // Relative paths, same convention as "image" below - the
            // storefront builds the full URL itself (see API Documentation
            // > Products). "image" stays the primary/thumbnail for
            // existing single-image UI; "images" is the full gallery.
            'image' => $this->image,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => $img->path)),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reusable, store-wide attribute (Size, Color, Storage, Flavor...) - defined
 * once under Settings > Attributes (or inline while creating a product) and
 * reused across as many products as apply. A product only ever uses a
 * subset of an attribute's values (see ProductVariant/the variant builder
 * UI) - not every product with a "Size" attribute needs every size this
 * store has ever sold.
 */
class ProductAttribute extends Model
{
    protected $fillable = ['name'];

    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}

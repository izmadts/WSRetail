<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Product;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    /**
     * The customer/storefront API is a retail-only channel - always
     * sale_price/is_retail, unconditionally. Not driven by customer_group -
     * see the same note on OrderController::resolveChannel.
     */
    public function index(Request $request)
    {
        $query = Product::active()->inStock()
            ->where('is_retail', true)
            ->with('category', 'variants')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get();

        $data = $products->map(function ($product) {
            $resource = (new ProductResource($product))->resolve();
            $resource['price'] = (float) ($product->has_variants ? $product->variants->min('sale_price') : $product->sale_price);
            $resource['price_field'] = 'sale_price';
            return $resource;
        });

        return $this->success($data->values());
    }
}

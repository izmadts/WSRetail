<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Product;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    /**
     * The customer (seller-app/Mandi) API is a wholesale-only channel -
     * always wholesale_price/is_wholesale, unconditionally. Not driven by
     * customer_group or by whether the customer has an agent - see the
     * same note on OrderController::resolveChannel.
     */
    public function index(Request $request)
    {
        $query = Product::active()->where('current_stock', '>', 0)
            ->where('is_wholesale', true)
            ->with('category')
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
            $resource['price'] = (float) $product->wholesale_price;
            $resource['price_field'] = 'wholesale_price';
            return $resource;
        });

        return $this->success($data->values());
    }
}

<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\Product;
use App\Models\Customer;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    /**
     * Returns every active, in-stock product with both prices and both
     * availability flags - the Flutter app is expected to filter client-side
     * by is_retail/is_wholesale the same way the web Add Sale form does
     * (see resources/views/admin/sales/create.blade.php's visibleProducts()),
     * so the catalog can be cached and filtered offline.
     *
     * Pass ?customer_id= to get that filtering done server-side instead -
     * only products valid for that customer's group are returned, and
     * "price" is already resolved to the correct column for convenience.
     *
     * A channel-restricted agent (User::channel) never sees the other
     * channel's products at all, regardless of ?customer_id= - e.g. a
     * wholesale-only agent's catalog excludes retail-only items even if
     * nothing else filtered them out.
     */
    public function index(Request $request)
    {
        $agent = $this->agent();
        $query = Product::active()->where('current_stock', '>', 0)->with('category')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($agent->channel) && $agent->channel !== 'both') {
            $query->where($agent->channel === 'wholesale' ? 'is_wholesale' : 'is_retail', true);
        }

        $priceField = 'sale_price';
        if ($request->filled('customer_id')) {
            $customer = Customer::where('id', $request->customer_id)
                ->where('created_by_agent_id', $agent->id)
                ->with('customerGroup')
                ->first();

            if (!$customer) {
                return $this->error('Customer not found or not accessible.', 404);
            }

            $priceField = $customer->customerGroup->price_field ?? 'sale_price';

            if (!$agent->allowsPriceField($priceField)) {
                return $this->error('This customer is outside your assigned channel.', 403);
            }

            $query->where($priceField === 'wholesale_price' ? 'is_wholesale' : 'is_retail', true);
        }

        $products = $query->get();

        $data = $products->map(function ($product) use ($priceField) {
            $resource = (new ProductResource($product))->resolve();
            $resource['price'] = $priceField === 'wholesale_price' ? (float) $product->wholesale_price : (float) $product->sale_price;
            $resource['price_field'] = $priceField;
            return $resource;
        });

        return $this->success($data->values());
    }
}

<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Category;
use App\Models\Product;

class CategoryController extends ApiController
{
    /**
     * Active categories that have at least one in-stock, wholesale-eligible
     * product - the customer API is a wholesale-only channel, always
     * (see the note on Api\Customer\OrderController::resolveChannel).
     */
    public function index()
    {
        $categoryIds = Product::active()
            ->where('current_stock', '>', 0)
            ->where('is_wholesale', true)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::active()
            ->whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $this->success($categories);
    }
}

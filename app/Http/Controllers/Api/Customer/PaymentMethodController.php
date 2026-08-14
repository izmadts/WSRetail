<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\PaymentMethod;

class PaymentMethodController extends ApiController
{
    /**
     * Checkout options to present to the shopper (Settings > Ecommerce >
     * Payment). Not a gateway list - just enabled name/description pairs;
     * the shopper's pick is sent back as free text on POST /orders
     * (payment_method).
     */
    public function index()
    {
        $methods = PaymentMethod::enabled()->ordered()->get(['id', 'code', 'name', 'description']);

        return $this->success($methods);
    }
}

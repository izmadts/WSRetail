<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

abstract class ApiController extends Controller
{
    use ApiResponseTrait;

    protected function customer()
    {
        return Auth::user();
    }
}

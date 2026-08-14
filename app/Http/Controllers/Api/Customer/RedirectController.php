<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\UrlRedirect;
use Illuminate\Http\Request;

class RedirectController extends ApiController
{
    /**
     * Looks up an old-store URL path and returns where the storefront
     * should permanently redirect it to. Public - no customer token - a
     * storefront needs to resolve this for an anonymous visitor who
     * followed a bookmarked or still-indexed old-store link, before any
     * login/connect has happened. See IntegrationImportService::
     * recordRedirect() for how these are captured, and the Next.js
     * storefront's app/product/[...slug]/page.tsx for the consumer.
     */
    public function lookup(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            return $this->error('The "path" query parameter is required.', 422);
        }

        $normalized = '/' . trim(parse_url($path, PHP_URL_PATH) ?? $path, '/');

        $redirect = UrlRedirect::where('old_path', $normalized)->first();

        if (!$redirect) {
            return $this->error('No redirect found for this path.', 404);
        }

        return $this->success([
            'old_path' => $redirect->old_path,
            'new_path' => $redirect->new_path,
            'product_id' => $redirect->product_id,
        ]);
    }
}

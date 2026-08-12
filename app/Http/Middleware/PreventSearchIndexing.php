<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is an internal business application, not a public site - adds
 * X-Robots-Tag to every response so compliant crawlers stay out even if
 * robots.txt itself is ever missed/cached wrong. Header-based, not just a
 * <meta> tag, so it also covers the API/JSON responses, not only HTML
 * pages.
 */
class PreventSearchIndexing
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');

        return $response;
    }
}

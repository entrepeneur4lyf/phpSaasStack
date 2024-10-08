<?php

declare(strict_types=1);

namespace Src\Middleware;

use Swoole\Http\Request;
use Swoole\Http\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        // HTTP Strict Transport Security (HSTS)
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Content Security Policy (CSP)
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; frame-ancestors 'none'; base-uri 'self';");

        // X-Frame-Options
        $response->header('X-Frame-Options', 'DENY');

        // X-Content-Type-Options
        $response->header('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection
        $response->header('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Clear-Site-Data (for logout)
        // Note: This should only be set on logout or similar scenarios
        // $response->header('Clear-Site-Data', '"cache", "cookies", "storage"');

        $next($request, $response);
    }
}

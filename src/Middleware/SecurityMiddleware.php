<?php
declare(strict_types=1);

namespace Src\Middleware;

use App\Config\Security;
use App\Config\RateLimiter;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SecurityMiddleware
{
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new RateLimiter(5, 60); // 5 attempts per minute
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        // CSRF protection
        if ($request->server['request_method'] !== 'GET') {
            if (!Security::validateCsrfToken($request)) {
                $response->status(403);
                $response->end(json_encode(['error' => 'Invalid CSRF token']));
                return;
            }
        }

        // XSS protection
        $request->post = Security::sanitizeInput($request->post ?? []);
        $request->get = Security::sanitizeInput($request->get ?? []);

        // Rate limiting for registration and login
        if (in_array($request->server['request_uri'], ['/register', '/login'])) {
            $ip = $request->server['remote_addr'];
            if (!$this->rateLimiter->attempt($ip)) {
                $response->status(429);
                $response->end(json_encode(['error' => 'Too many attempts. Please try again later.']));
                return;
            }
        }

        // Security headers
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Generate new CSRF token for the next request
        $newCsrfToken = Security::generateCsrfToken();
        $_SESSION['csrf_token'] = $newCsrfToken;
        $response->header('X-CSRF-TOKEN', $newCsrfToken);

        $next($request, $response);
    }
}
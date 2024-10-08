<?php

declare(strict_types=1);

namespace Src\Middleware;

use App\Utils\RateLimiter;
use Swoole\Http\Request;
use Swoole\Http\Response;

class LoginRateLimitMiddleware
{
    private RateLimiter $rateLimiter;

    public function __construct(int $limit, int $window)
    {
        $this->rateLimiter = new RateLimiter($limit, $window);
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $ip = $request->server['remote_addr'];
        $username = $request->post['username'] ?? '';

        // Rate limit by IP and username combination
        $key = "login:{$ip}:{$username}";

        if (!$this->rateLimiter->attempt($key)) {
            $response->status(429);
            $response->end(json_encode(['error' => 'Too many login attempts. Please try again later.']));
            return;
        }

        $next($request, $response);
    }
}

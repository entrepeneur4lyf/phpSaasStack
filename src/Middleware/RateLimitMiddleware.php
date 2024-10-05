<?php
declare(strict_types=1);

namespace Src\Middleware;

use Src\Utils\RateLimiter;
use Swoole\Http\Request;
use Swoole\Http\Response;

class RateLimitMiddleware
{
    private RateLimiter $rateLimiter;

    public function __construct(int $limit, int $window)
    {
        $this->rateLimiter = new RateLimiter($limit, $window);
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $ip = $request->server['remote_addr'];
        
        if (!$this->rateLimiter->attempt($ip)) {
            $response->status(429);
            $response->end(json_encode(['error' => 'Too many requests. Please try again later.']));
            return;
        }

        $next($request, $response);
    }
}
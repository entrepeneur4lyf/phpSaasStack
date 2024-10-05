<?php
declare(strict_types=1);

namespace Src\Middleware;

use App\Utils\Logger;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SecurityLoggingMiddleware
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        // Log the request
        $this->logger->info('Incoming request', [
            'method' => $request->server['request_method'],
            'uri' => $request->server['request_uri'],
            'ip' => $request->server['remote_addr'],
            'user_agent' => $request->header['user-agent'] ?? 'Unknown'
        ]);

        // Call the next middleware/handler
        $next($request, $response);

        // Log the response
        $this->logger->info('Outgoing response', [
            'status' => $response->getStatusCode()
        ]);
    }
}
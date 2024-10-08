<?php

declare(strict_types=1);

namespace Src\Middleware;

use Swoole\Http\Request;
use Swoole\Http\Response;

class CorsMiddleware
{
    private array $corsConfig;

    public function __construct(array $corsConfig)
    {
        $this->corsConfig = $corsConfig;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $origin = $request->header['origin'] ?? '';

        if (in_array($origin, $this->corsConfig['allowed_origins'])) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', implode(', ', $this->corsConfig['allowed_methods']));
            $response->header('Access-Control-Allow-Headers', implode(', ', $this->corsConfig['allowed_headers']));

            if (!empty($this->corsConfig['exposed_headers'])) {
                $response->header('Access-Control-Expose-Headers', implode(', ', $this->corsConfig['exposed_headers']));
            }

            if ($this->corsConfig['max_age'] > 0) {
                $response->header('Access-Control-Max-Age', (string)$this->corsConfig['max_age']);
            }

            if ($this->corsConfig['supports_credentials']) {
                $response->header('Access-Control-Allow-Credentials', 'true');
            }
        }

        if ($request->server['request_method'] === 'OPTIONS') {
            $response->status(204);
            $response->end();
            return;
        }

        $next($request, $response);
    }
}

<?php
declare(strict_types=1);

namespace Src\Middleware;

use Swoole\Http\Request;
use Swoole\Http\Response;

class HttpsRedirectMiddleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        if (!$request->server['https']) {
            $url = "https://{$request->header['host']}{$request->server['request_uri']}";
            $response->redirect($url, 301);
            return;
        }

        $next($request, $response);
    }
}
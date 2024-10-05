<?php
declare(strict_types=1);

namespace Src\Middleware;

use App\Utils\InputSanitizer;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SanitizationMiddleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        $request->get = InputSanitizer::sanitizeArray($request->get ?? []);
        $request->post = InputSanitizer::sanitizeArray($request->post ?? []);
        $request->cookie = InputSanitizer::sanitizeArray($request->cookie ?? []);

        $next($request, $response);
    }
}
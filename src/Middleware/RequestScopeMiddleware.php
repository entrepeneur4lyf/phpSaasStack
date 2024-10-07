<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Config\Dependencies;

class RequestScopeMiddleware
{
    public function process($request, $handler)
    {
        // Clear any existing request-scoped services
        Dependencies::clearRequestScopedServices();

        // Process the request
        $response = $handler->handle($request);

        // Clear request-scoped services after the request is processed
        Dependencies::clearRequestScopedServices();

        return $response;
    }
}
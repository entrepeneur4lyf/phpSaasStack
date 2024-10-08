<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Core\SessionManager;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SessionMiddleware
{
    private SessionManager $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function process(Request $request, Response $response, callable $next): void
    {
        // Start the session and attach it to the request
        $sessionData = $this->sessionManager->startSession($request, $response);
        $request->session = $sessionData;

        // Call the next middleware or controller
        $next($request, $response);

        // Save the session after the response has been processed
        $this->sessionManager->saveSession($request->session, $response);
    }
}

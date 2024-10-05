<?php
declare(strict_types=1);

namespace Src\Middleware;

use App\Utils\SessionManager;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SessionMiddleware
{
    private SessionManager $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $sessionData = $this->sessionManager->startSession($request, $response);
        $request->session = $sessionData;

        $next($request, $response);

        $this->sessionManager->saveSession($request->session, $response);
    }
}
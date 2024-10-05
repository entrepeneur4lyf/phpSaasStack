<?php
declare(strict_types=1);

namespace Src\Middleware;

use Swoole\Http\Request;
use Swoole\Http\Response;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        if (!isset($request->user->role_name) || !in_array($request->user->role_name, $this->allowedRoles)) {
            $response->status(403);
            $response->end(json_encode(['error' => 'Access denied']));
            return;
        }

        $next($request, $response);
    }
}
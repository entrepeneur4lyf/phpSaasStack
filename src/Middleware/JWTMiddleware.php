<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Interfaces\JWTServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class JWTMiddleware
{
    public function __construct(
        private readonly JWTServiceInterface $jwtService
    ) {}

    public function __invoke(Request $request, Response $response, callable $next): void
    {
        $token = $this->extractToken($request);

        if (!$token || !$this->jwtService->validateToken($token)) {
            $this->sendUnauthorizedResponse($response);
            return;
        }

        $request->user = $this->jwtService->getPayload($token);
        $next($request, $response);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header['authorization'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function sendUnauthorizedResponse(Response $response): void
    {
        $response->status(401);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['error' => 'Unauthorized']));
    }
}
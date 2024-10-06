<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Interfaces\JWTServiceInterface;
use Src\Exceptions\UnauthorizedException;

class JWTAuthMiddleware
{
    public function __construct(private JWTServiceInterface $jwtService) {}

    public function process($request, $response, $next)
    {
        $token = $this->extractToken($request);

        if (!$token || !$this->jwtService->validateToken($token)) {
            throw new UnauthorizedException('Invalid or expired token');
        }

        $payload = $this->jwtService->getPayload($token);
        $request->user = $payload['user'] ?? null;

        return $next($request, $response);
    }

    private function extractToken($request)
    {
        $header = $request->header['Authorization'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\JWTServiceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService implements JWTServiceInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $algorithm = 'HS256'
    ) {}

    public function validateToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getPayload(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function generateToken(array $payload): string
    {
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }
}
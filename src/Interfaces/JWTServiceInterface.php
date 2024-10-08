<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface JWTServiceInterface
{
    public function validateToken(string $token): bool;
    public function getPayload(string $token): array;
    public function generateToken(array $payload): string;
}

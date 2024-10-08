<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\AuthServiceInterface;
use Src\Interfaces\UserServiceInterface;
use Src\Interfaces\JWTServiceInterface;
use Src\Models\User;
use Src\Exceptions\AuthenticationException;

class AuthService implements AuthServiceInterface
{
    private ?User $currentUser = null;

    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly JWTServiceInterface $jwtService
    ) {
    }

    public function login(string $email, string $password): ?string
    {
        $user = $this->userService->getUserByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            $this->currentUser = $user;
            return $this->jwtService->generateToken([
                'user_id' => $user->getId(),
                'email' => $user->getEmail()
            ]);
        }

        throw new AuthenticationException('Invalid credentials');
    }

    public function logout(): void
    {
        // Since we're using JWT, we don't need to do anything server-side for logout
        // The client should discard the token
        $this->currentUser = null;
    }

    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUser !== null;
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->hasRole($role);
    }

    public function validateToken(string $token): bool
    {
        return $this->jwtService->validateToken($token);
    }

    public function getUserFromToken(string $token): ?User
    {
        if ($this->validateToken($token)) {
            $payload = $this->jwtService->getPayload($token);
            $userId = $payload['user_id'] ?? null;
            if ($userId) {
                return $this->userService->getUserById($userId);
            }
        }
        return null;
    }
}

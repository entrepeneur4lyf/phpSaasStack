<?php

declare(strict_types=1);

namespace Src\Services;

use App\Interfaces\AuthServiceInterface;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use App\Exceptions\AuthenticationException;

class AuthService implements AuthServiceInterface
{
    private ?User $currentUser = null;

    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    public function login(string $email, string $password): ?User
    {
        // Login logic
    }

    public function logout(): void
    {
        // Logout logic
    }

    public function getCurrentUser(): ?User
    {
        if ($this->currentUser === null && isset($_SESSION['user_id'])) {
            $this->currentUser = $this->userService->getUserById($_SESSION['user_id']);
        }
        return $this->currentUser;
    }

    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->hasRole($role);
    }

    private function startSession(User $user): void
    {
        session_start();
        $_SESSION['user_id'] = $user->getId();
        // You might want to regenerate session ID for security
        session_regenerate_id(true);
    }

    private function endSession(): void
    {
        session_unset();
        session_destroy();
    }
}
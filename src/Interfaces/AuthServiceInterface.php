<?php

namespace Src\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    public function login(string $email, string $password): ?User;
    public function logout(): void;
    public function getCurrentUser(): ?User;
    public function isAuthenticated(): bool;
    public function hasRole(string $role): bool;
}

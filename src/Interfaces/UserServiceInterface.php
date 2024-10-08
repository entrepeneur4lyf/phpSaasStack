<?php

namespace Src\Interfaces;

use App\Models\User;

interface UserServiceInterface
{
    public function createUser(array $userData): User;
    public function getUserById(int $id): ?User;
    public function updateUser(User $user, array $userData): bool;
    public function deleteUser(User $user): bool;
}

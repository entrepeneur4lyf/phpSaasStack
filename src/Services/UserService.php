<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\UserServiceInterface;
use Src\Models\User;
use Src\Exceptions\UserNotFoundException;
use Src\Exceptions\ValidationException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly User $userModel,
        private readonly AuthService $authService
    ) {}

    public function createUser(array $userData): array
    {
        // Validate user data
        $this->validateUserData($userData);

        // Hash password
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Create user
        $userId = $this->userModel->create($userData);
        return $this->getUserById($userId);
    }

    public function getUserById(int $userId): array
    {
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new UserNotFoundException("User not found with ID: $userId");
        }
        return $user;
    }

    public function updateUser(int $userId, array $userData): array
    {
        // Validate user data
        $this->validateUserData($userData, true);

        // Update user
        $success = $this->userModel->update($userId, $userData);
        if (!$success) {
            throw new \RuntimeException("Failed to update user with ID: $userId");
        }
        return $this->getUserById($userId);
    }

    public function deleteUser(int $userId): bool
    {
        return $this->userModel->delete($userId);
    }

    public function loginUser(string $email, string $password): string
    {
        return $this->authService->authenticate($email, $password);
    }

    private function validateUserData(array $userData, bool $isUpdate = false): void
    {
        $requiredFields = $isUpdate ? [] : ['email', 'password', 'name'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                throw new ValidationException("$field is required");
            }
        }

        if (isset($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email format");
        }

        if (isset($userData['password']) && strlen($userData['password']) < 8) {
            throw new ValidationException("Password must be at least 8 characters long");
        }
    }
}
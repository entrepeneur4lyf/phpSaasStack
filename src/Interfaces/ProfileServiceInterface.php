<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface ProfileServiceInterface
{
    public function getProfileByUserId(int $userId): ?array;
    public function updateProfile(int $userId, array $profileData): bool;
    // Add other methods as needed
}
<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface ProfileServiceInterface
{
    public function getProfileByUserId(int $userId): ?array;
    public function updateProfile(int $userId, array $profileData): bool;
    public function createProfile(int $userId, array $profileData): bool;
    public function deleteProfile(int $userId): bool;
    public function getPublicProfile(int $userId): ?array;
    public function updateProfilePicture(int $userId, string $imagePath): bool;
    public function getProfileStats(int $userId): array;
    public function searchProfiles(array $criteria, int $limit = 20, int $offset = 0): array;
    public function validateProfileData(array $profileData): bool;
    public function getRecentlyActiveProfiles(int $limit = 10): array;
}

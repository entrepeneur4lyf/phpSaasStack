<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface LicenseServiceInterface
{
    public function createLicense(array $licenseData): int;
    public function getLicenseById(int $licenseId): ?array;
    public function getLicensesByProductId(int $productId): array;
    public function getLicensesByUserId(int $userId): array;
    public function updateLicense(int $licenseId, array $licenseData): bool;
    public function deleteLicense(int $licenseId): bool;
    public function validateLicense(int $licenseId, int $userId): bool;
    public function extendLicense(int $licenseId, int $durationInDays): bool;
    public function revokeLicense(int $licenseId): bool;
}

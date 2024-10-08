<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\LicenseServiceInterface;
use Src\Models\License;
use Src\Exceptions\ValidationException;

class LicenseService implements LicenseServiceInterface
{
    public function __construct(
        private readonly License $licenseModel
    ) {
    }

    public function createLicense(array $licenseData): int
    {
        $this->validateLicenseData($licenseData);
        return $this->licenseModel->create($licenseData);
    }

    public function getLicenseById(int $licenseId): ?array
    {
        return $this->licenseModel->getById($licenseId);
    }

    public function getLicensesByProductId(int $productId): array
    {
        return $this->licenseModel->getByProductId($productId);
    }

    public function getLicensesByUserId(int $userId): array
    {
        return $this->licenseModel->getByUserId($userId);
    }

    public function updateLicense(int $licenseId, array $licenseData): bool
    {
        $this->validateLicenseData($licenseData, true);
        return $this->licenseModel->update($licenseId, $licenseData);
    }

    public function deleteLicense(int $licenseId): bool
    {
        return $this->licenseModel->delete($licenseId);
    }

    public function validateLicense(int $licenseId, int $userId): bool
    {
        $license = $this->licenseModel->getById($licenseId);
        if (!$license) {
            return false;
        }

        if ($license['user_id'] !== $userId) {
            return false;
        }

        if (strtotime($license['expiration_date']) < time()) {
            return false;
        }

        return true;
    }

    public function extendLicense(int $licenseId, int $durationInDays): bool
    {
        $license = $this->licenseModel->getById($licenseId);
        if (!$license) {
            return false;
        }

        $newExpirationDate = date('Y-m-d H:i:s', strtotime($license['expiration_date'] . " +{$durationInDays} days"));
        return $this->licenseModel->update($licenseId, ['expiration_date' => $newExpirationDate]);
    }

    public function revokeLicense(int $licenseId): bool
    {
        return $this->licenseModel->update($licenseId, ['status' => 'revoked']);
    }

    private function validateLicenseData(array $licenseData, bool $isUpdate = false): void
    {
        $requiredFields = $isUpdate ? [] : ['product_id', 'user_id', 'license_key', 'expiration_date'];
        foreach ($requiredFields as $field) {
            if (empty($licenseData[$field])) {
                throw new ValidationException("$field is required");
            }
        }

        if (isset($licenseData['license_key']) && strlen($licenseData['license_key']) !== 32) {
            throw new ValidationException("License key must be 32 characters long");
        }

        if (isset($licenseData['expiration_date']) && !strtotime($licenseData['expiration_date'])) {
            throw new ValidationException("Invalid expiration date format");
        }

        // Add more validation as needed
    }
}

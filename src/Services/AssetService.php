<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\AssetServiceInterface;
use Src\Interfaces\FileDeliveryServiceInterface;
use Src\Models\Asset;
use Src\Exceptions\ValidationException;
use Src\Exceptions\FileUploadException;
use Intervention\Image\ImageManager;

class AssetService implements AssetServiceInterface
{
    private ImageManager $imageManager;

    public function __construct(
        private readonly Asset $assetModel,
        private readonly FileDeliveryServiceInterface $fileDeliveryService
    ) {
        $this->imageManager = new ImageManager(['driver' => 'gd']);
    }

    public function uploadAsset(array $fileData, int $userId): int
    {
        $this->validateFileUpload($fileData);
        $filePath = $this->moveUploadedFile($fileData);
        $metadata = $this->getFileMetadata($filePath);

        $assetData = [
            'user_id' => $userId,
            'file_name' => $fileData['name'],
            'file_path' => $filePath,
            'file_type' => $fileData['type'],
            'file_size' => $fileData['size'],
            'metadata' => json_encode($metadata),
        ];

        return $this->assetModel->create($assetData);
    }

    public function getAssetById(int $assetId): ?array
    {
        return $this->assetModel->getById($assetId);
    }

    public function getAssetsByUserId(int $userId): array
    {
        return $this->assetModel->getByUserId($userId);
    }

    public function updateAsset(int $assetId, array $assetData): bool
    {
        $this->validateAssetData($assetData);
        return $this->assetModel->update($assetId, $assetData);
    }

    public function deleteAsset(int $assetId): bool
    {
        $asset = $this->getAssetById($assetId);
        if ($asset && file_exists($asset['file_path'])) {
            unlink($asset['file_path']);
        }
        return $this->assetModel->delete($assetId);
    }

    public function getAssetDownloadUrl(int $assetId, int $userId): string
    {
        if (!$this->validateAssetAccess($assetId, $userId)) {
            throw new \RuntimeException('User does not have access to this asset');
        }
        return $this->fileDeliveryService->generateDownloadUrl($assetId, $userId);
    }

    public function validateAssetAccess(int $assetId, int $userId): bool
    {
        return $this->fileDeliveryService->validateDownloadAccess($assetId, $userId);
    }

    public function cropImage(int $assetId, array $cropData): bool
    {
        $asset = $this->getAssetById($assetId);
        if (!$asset || !in_array($asset['file_type'], ['image/jpeg', 'image/png', 'image/gif'])) {
            throw new ValidationException('Invalid asset or unsupported image type');
        }

        try {
            $image = $this->imageManager->make($asset['file_path']);

            $image->crop(
                (int)$cropData['width'],
                (int)$cropData['height'],
                (int)$cropData['x'],
                (int)$cropData['y']
            );

            $image->save($asset['file_path']);

            // Update asset metadata
            $newMetadata = $this->getFileMetadata($asset['file_path']);
            $this->assetModel->update($assetId, ['metadata' => json_encode($newMetadata)]);

            return true;
        } catch (\Exception $e) {
            // Log the error
            error_log('Image cropping failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getAssetMetadata(int $assetId): array
    {
        return $this->fileDeliveryService->getAssetMetadata($assetId);
    }

    private function validateFileUpload(array $fileData): void
    {
        if (!isset($fileData['error']) || is_array($fileData['error'])) {
            throw new FileUploadException('Invalid file upload parameters');
        }

        switch ($fileData['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new FileUploadException('No file uploaded');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileUploadException('File size exceeds limit');
            default:
                throw new FileUploadException('Unknown file upload error');
        }

        if ($fileData['size'] > 10000000) {
            throw new FileUploadException('File size exceeds 10MB limit');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/zip'];
        if (!in_array($fileData['type'], $allowedTypes)) {
            throw new FileUploadException('File type not allowed');
        }
    }

    private function moveUploadedFile(array $fileData): string
    {
        $uploadDir = '/path/to/upload/directory/';
        $fileName = uniqid() . '_' . $fileData['name'];
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new FileUploadException('Failed to move uploaded file');
        }

        return $filePath;
    }

    private function getFileMetadata(string $filePath): array
    {
        $metadata = [];

        if (function_exists('exif_read_data') && in_array(mime_content_type($filePath), ['image/jpeg', 'image/tiff'])) {
            $exif = @exif_read_data($filePath);
            if ($exif !== false) {
                $metadata['exif'] = $exif;
            }
        }

        $metadata['dimensions'] = getimagesize($filePath);

        return $metadata;
    }

    private function validateAssetData(array $assetData): void
    {
        // This method can be used for any additional business logic validation
        // that goes beyond simple data type and format checks
        // For example, checking if a file name is unique
    }
}

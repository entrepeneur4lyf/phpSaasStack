<?php
declare(strict_types=1);

namespace Src\Utils;

use Exception;

class FileUploadHandler
{
    private string $uploadDir;
    private array $allowedExtensions;
    private int $maxFileSize;

    public function __construct(string $uploadDir, array $allowedExtensions, int $maxFileSize)
    {
        $this->uploadDir = $uploadDir;
        $this->allowedExtensions = $allowedExtensions;
        $this->maxFileSize = $maxFileSize;
    }

    public function handleUpload(array $file): string
    {
        $this->validateFile($file);

        $fileName = $this->generateUniqueFileName($file['name']);
        $destination = $this->uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file.');
        }

        return $fileName;
    }

    private function validateFile(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File size exceeds the maximum allowed size.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception('File type not allowed.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('File is not a valid uploaded file.');
        }

        // Perform additional checks (e.g., MIME type verification)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception('File MIME type not allowed.');
        }
    }

    private function generateUniqueFileName(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return uniqid() . '.' . $extension;
    }
}
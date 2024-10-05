<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\FileDeliveryService;
use App\Services\AuthService;

class FileDeliveryController extends BaseController
{
    protected $twig;
    protected $fileDeliveryService;
    protected $authService;

    public function __construct(Environment $twig, FileDeliveryService $fileDeliveryService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->fileDeliveryService = $fileDeliveryService;
        $this->authService = $authService;
    }

    public function download($fileId)
    {
        $user = $this->authService->getUser();
        $file = $this->fileDeliveryService->getFileById($fileId);

        if (!$file) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        if (!$this->fileDeliveryService->canUserAccessFile($user->id, $fileId)) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $filePath = $this->fileDeliveryService->getFilePath($fileId);
        return $this->response->download($filePath, null);
    }

    public function streamVideo($videoId)
    {
        $user = $this->authService->getUser();
        $video = $this->fileDeliveryService->getVideoById($videoId);

        if (!$video) {
            return $this->response->setStatusCode(404)->setBody('Video not found');
        }

        if (!$this->fileDeliveryService->canUserAccessVideo($user->id, $videoId)) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $videoPath = $this->fileDeliveryService->getVideoPath($videoId);
        return $this->response->setHeader('Content-Type', 'video/mp4')
                              ->setHeader('Accept-Ranges', 'bytes')
                              ->setBody(file_get_contents($videoPath));
    }
}
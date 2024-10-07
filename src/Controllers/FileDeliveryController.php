<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\FileDeliveryService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class FileDeliveryController extends BaseController
{
    protected FileDeliveryService $fileDeliveryService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, FileDeliveryService $fileDeliveryService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->fileDeliveryService = $fileDeliveryService;
        $this->authService = $authService;
    }

    public function download(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $fileId = $args['fileId'] ?? null;
        $file = $this->fileDeliveryService->getFileById($fileId);

        if (!$file) {
            $this->jsonResponse($response, ['error' => 'File not found'], 404);
            return;
        }

        if (!$this->fileDeliveryService->canUserAccessFile($user->id, $fileId)) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $filePath = $this->fileDeliveryService->getFilePath($fileId);
        $response->header('Content-Type', mime_content_type($filePath));
        $response->header('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
        $response->sendfile($filePath);
    }

    public function streamVideo(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $videoId = $args['videoId'] ?? null;
        $video = $this->fileDeliveryService->getVideoById($videoId);

        if (!$video) {
            $this->jsonResponse($response, ['error' => 'Video not found'], 404);
            return;
        }

        if (!$this->fileDeliveryService->canUserAccessVideo($user->id, $videoId)) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $videoPath = $this->fileDeliveryService->getVideoPath($videoId);
        $response->header('Content-Type', 'video/mp4');
        $response->header('Accept-Ranges', 'bytes');
        
        // Implement range requests for video streaming
        $fileSize = filesize($videoPath);
        $range = $request->header['range'] ?? '';
        if ($range) {
            list($start, $end) = explode('-', substr($range, 6));
            $start = intval($start);
            $end = $end ? intval($end) : $fileSize - 1;
            $length = $end - $start + 1;
            $response->header('HTTP/1.1 206 Partial Content');
            $response->header('Content-Range', "bytes $start-$end/$fileSize");
            $response->header('Content-Length', $length);
            $response->sendfile($videoPath, $start, $length);
        } else {
            $response->header('Content-Length', $fileSize);
            $response->sendfile($videoPath);
        }
    }
}
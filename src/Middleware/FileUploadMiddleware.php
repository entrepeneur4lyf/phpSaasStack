<?php
declare(strict_types=1);

namespace Src\Middleware;

use App\Utils\FileUploadHandler;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Exception;

class FileUploadMiddleware
{
    private FileUploadHandler $fileUploadHandler;

    public function __construct(FileUploadHandler $fileUploadHandler)
    {
        $this->fileUploadHandler = $fileUploadHandler;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        if ($request->files && !empty($request->files['file'])) {
            try {
                $uploadedFileName = $this->fileUploadHandler->handleUpload($request->files['file']);
                $request->uploadedFile = $uploadedFileName;
            } catch (Exception $e) {
                $response->status(400);
                $response->end(json_encode(['error' => $e->getMessage()]));
                return;
            }
        }

        $next($request, $response);
    }
}
<?php

declare(strict_types=1);

namespace Src\Core;

use Monolog\Logger;
use Throwable;
use Swoole\Http\Response;

class ErrorHandler
{
    public function __construct(private readonly Logger $logger)
    {
    }

    public function handleException(Throwable $exception, Response $response): void
    {
        $this->logException($exception);
        $this->sendErrorResponse($exception, $response);
    }

    private function logException(Throwable $exception): void
    {
        $this->logger->error(
            $exception->getMessage(),
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );
    }

    private function sendErrorResponse(Throwable $exception, Response $response): void
    {
        $statusCode = $this->getStatusCodeForException($exception);
        $response->status($statusCode);

        if ($statusCode === 404) {
            $this->render($response, 'errors/404');
        } elseif ($statusCode === 403) {
            $this->render($response, 'errors/403');
        } else {
            $this->render($response, 'errors/500');
        }
    }

    private function getStatusCodeForException(Throwable $exception): int
    {
        // Map exception types to appropriate status codes
        return match (true) {
            $exception instanceof \Src\Exceptions\NotFoundException => 404,
            $exception instanceof \Src\Exceptions\ForbiddenException => 403,
            default => 500,
        };
    }

    private function render(Response $response, string $view, array $data = []): void
    {
        // Implement a simple rendering mechanism for error pages
        $content = file_get_contents(__DIR__ . "/../Views/{$view}.php");
        $response->end($content);
    }
}

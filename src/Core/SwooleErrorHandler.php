<?php

declare(strict_types=1);

namespace Src\Core;

use Psr\Log\LoggerInterface;
use Src\Services\ErrorReportingService;
use Swoole\Http\Response;

class SwooleErrorHandler
{
    private LoggerInterface $logger;
    private ErrorReportingService $errorReportingService;
    private CustomErrorHandler $errorHandler;

    public function __construct(
        LoggerInterface $logger,
        ErrorReportingService $errorReportingService,
        CustomErrorHandler $errorHandler
    ) {
        $this->logger = $logger;
        $this->errorReportingService = $errorReportingService;
        $this->errorHandler = $errorHandler;
    }

    public function handleException(\Throwable $exception, Response $response): void
    {
        $this->logger->error('Uncaught exception: ' . $exception->getMessage(), [
            'exception' => $exception,
        ]);

        $this->errorReportingService->reportCriticalError($exception);

        $content = $this->errorHandler->render($exception);
        $response->header('Content-Type', 'text/html');
        $response->status($this->getStatusCode($exception));
        $response->end($content);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        return $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }

        $exception = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        $this->logger->error('PHP Error: ' . $errstr, [
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline,
        ]);

        if ($this->shouldReport($errno)) {
            $this->errorReportingService->reportError($exception);
        }

        return true;
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && $this->isFatalError($error['type'])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private function shouldReport(int $errno): bool
    {
        return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true);
    }

    private function isFatalError(int $type): bool
    {
        return in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true);
    }

    public function renderHttpException(Response $response, \Throwable $exception): void
    {
        $statusCode = $this->getStatusCode($exception);
        $response->status($statusCode);
        $response->header('Content-Type', 'text/html');
        $response->end($this->errorHandler->render($exception));
    }

    public function renderJsonException(Response $response, \Throwable $exception): void
    {
        $statusCode = $this->getStatusCode($exception);
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]
        ]));
    }
}

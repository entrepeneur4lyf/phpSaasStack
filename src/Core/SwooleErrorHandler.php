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

    // ... other methods ...
}
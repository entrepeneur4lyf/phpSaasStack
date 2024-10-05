<?php

declare(strict_types=1);

namespace Src\Services;

use Psr\Log\LoggerInterface;
use Src\Config\Config;
use Rollbar\Rollbar;
use Rollbar\Payload\Level;

class ErrorReportingService
{
    private LoggerInterface $logger;
    private Config $config;

    public function __construct(LoggerInterface $logger, Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function reportCriticalError(\Throwable $error): void
    {
        $this->logger->critical($error->getMessage(), [
            'exception' => $error,
            'trace' => $error->getTraceAsString(),
        ]);

        Rollbar::log(Level::CRITICAL, $error);
    }

    public function reportError(\Throwable $error): void
    {
        $this->logger->error($error->getMessage(), [
            'exception' => $error,
            'trace' => $error->getTraceAsString(),
        ]);

        Rollbar::log(Level::ERROR, $error);
    }

    public function reportWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);

        Rollbar::log(Level::WARNING, $message, $context);
    }

    public function reportInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);

        Rollbar::log(Level::INFO, $message, $context);
    }
}
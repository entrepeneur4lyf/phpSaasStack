<?php

declare(strict_types=1);

namespace Src\Services;

use Psr\Log\LoggerInterface;
use Src\Config\ConfigurationManager;
use Src\Interfaces\ErrorReporterInterface;
use Rollbar\Rollbar;
use Rollbar\Payload\Level;

class ErrorReportingService implements ErrorReporterInterface
{
    private LoggerInterface $logger;
    private ConfigurationManager $config;
    private bool $rollbarInitialized = false;

    public function __construct(LoggerInterface $logger, ConfigurationManager $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeRollbar();
    }

    private function initializeRollbar(): void
    {
        $errorReportingService = $this->config->get('app.error_reporting.service');
        if ($errorReportingService === 'rollbar' && !$this->rollbarInitialized) {
            $rollbarConfig = $this->config->get('app.error_reporting.rollbar', []);
            if (!empty($rollbarConfig['access_token'])) {
                Rollbar::init($rollbarConfig);
                $this->rollbarInitialized = true;
            }
        }
    }

    public function reportCriticalError(\Throwable $error): void
    {
        $this->logger->critical($error->getMessage(), [
            'exception' => $error,
            'trace' => $error->getTraceAsString(),
        ]);

        $this->reportToRollbar(Level::CRITICAL, $error);
    }

    public function reportError(\Throwable $error): void
    {
        $this->logger->error($error->getMessage(), [
            'exception' => $error,
            'trace' => $error->getTraceAsString(),
        ]);

        $this->reportToRollbar(Level::ERROR, $error);
    }

    public function reportWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);

        $this->reportToRollbar(Level::WARNING, $message, $context);
    }

    public function reportInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);

        $this->reportToRollbar(Level::INFO, $message, $context);
    }

    private function reportToRollbar($level, $data, array $context = []): void
    {
        if ($this->rollbarInitialized) {
            if ($data instanceof \Throwable) {
                Rollbar::log($level, $data);
            } else {
                Rollbar::log($level, $data, $context);
            }
        }
    }
}
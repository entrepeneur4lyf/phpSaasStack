<?php

declare(strict_types=1);

namespace Src\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private MonologLogger $logger;

    public function __construct(string $name, string $logFile)
    {
        $this->logger = new MonologLogger($name);

        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        $streamHandler = new StreamHandler($logFile, MonologLogger::DEBUG);
        $streamHandler->setFormatter($formatter);

        $rotatingHandler = new RotatingFileHandler($logFile, 0, MonologLogger::DEBUG);
        $rotatingHandler->setFormatter($formatter);

        $this->logger->pushHandler($streamHandler);
        $this->logger->pushHandler($rotatingHandler);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }
}

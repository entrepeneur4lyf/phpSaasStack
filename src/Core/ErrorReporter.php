<?php

namespace Src\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class ErrorReporter
{
    private Logger $logger;
    private Swift_Mailer $mailer;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeLogger();
        $this->initializeMailer();
    }

    private function initializeLogger(): void
    {
        $this->logger = new Logger('error_reporter');
        $this->logger->pushHandler(new StreamHandler(STORAGE_PATH . '/logs/error_reports.log', Logger::ERROR));
    }

    private function initializeMailer(): void
    {
        $transport = (new Swift_SmtpTransport($this->config['smtp_host'], $this->config['smtp_port']))
            ->setUsername($this->config['smtp_username'])
            ->setPassword($this->config['smtp_password']);

        $this->mailer = new Swift_Mailer($transport);
    }

    public function reportError(\Throwable $error): void
    {
        $this->logError($error);
        $this->sendErrorEmail($error);
    }

    private function logError(\Throwable $error): void
    {
        $this->logger->error($error->getMessage(), [
            'exception' => $error,
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);
    }

    private function sendErrorEmail(\Throwable $error): void
    {
        $message = (new Swift_Message('Critical Error Report'))
            ->setFrom([$this->config['from_email'] => $this->config['from_name']])
            ->setTo($this->config['to_email'])
            ->setBody($this->formatErrorEmail($error), 'text/html');

        $this->mailer->send($message);
    }

    private function formatErrorEmail(\Throwable $error): string
    {
        $html = "<h1>Critical Error Report</h1>";
        $html .= "<p><strong>Error:</strong> " . htmlspecialchars($error->getMessage()) . "</p>";
        $html .= "<p><strong>File:</strong> " . htmlspecialchars($error->getFile()) . "</p>";
        $html .= "<p><strong>Line:</strong> " . $error->getLine() . "</p>";
        $html .= "<h2>Stack Trace:</h2>";
        $html .= "<pre>" . htmlspecialchars($error->getTraceAsString()) . "</pre>";
        return $html;
    }
}
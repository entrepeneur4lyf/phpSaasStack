<?php

namespace Src\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class ErrorReporter
{
    private LoggerInterface $logger;
    private MailerInterface $mailer;
    private array $config;

    public function __construct(LoggerInterface $logger, MailerInterface $mailer, array $config)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->config = $config;
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
        $email = (new Email())
            ->from($this->config['from_email'])
            ->to($this->config['to_email'])
            ->subject('Critical Error Report')
            ->html($this->formatErrorEmail($error));

        $this->mailer->send($email);
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
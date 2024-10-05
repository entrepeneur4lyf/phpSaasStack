<?php

declare(strict_types=1);

namespace Src\Core;

use Symfony\Component\ErrorHandler\ErrorHandler;
use Twig\Environment;
use Src\Interfaces\ErrorReporterInterface;

class CustomErrorHandler extends ErrorHandler
{
    private Environment $twig;
    private bool $debug;
    private ErrorReporterInterface $errorReporter;

    public function __construct(
        Environment $twig,
        ErrorReporterInterface $errorReporter,
        bool $debug = false
    ) {
        parent::__construct();
        $this->twig = $twig;
        $this->debug = $debug;
        $this->errorReporter = $errorReporter;
    }

    public function render(\Throwable $exception): string
    {
        $this->errorReportingService->reportError($exception);

        $statusCode = $this->getStatusCode($exception);
        $context = [
            'status_code' => $statusCode,
            'status_text' => $this->getStatusText($statusCode),
            'exception' => $exception,
            'debug' => $this->debug,
        ];

        if ($this->debug) {
            $context['exception_class'] = get_class($exception);
            $context['file'] = $exception->getFile();
            $context['line'] = $exception->getLine();
            $context['trace'] = $this->getFormattedTrace($exception);
            $context['previous_exceptions'] = $this->getPreviousExceptions($exception);
        }

        try {
            $content = $this->twig->render($this->debug ? "errors/exception_full.twig" : "errors/{$statusCode}.twig", $context);
        } catch (\Twig\Error\LoaderError $e) {
            $content = $this->twig->render('errors/generic.twig', $context);
        }

        return $content;
    }

    private function getStatusCode(\Throwable $exception): int
    {
        return $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;
    }

    private function getStatusText(int $statusCode): string
    {
        return [
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ][$statusCode] ?? 'Unknown Error';
    }

    private function getFormattedTrace(\Throwable $exception): array
    {
        $trace = $exception->getTrace();
        $formattedTrace = [];

        foreach ($trace as $entry) {
            $formattedTrace[] = [
                'file' => $entry['file'] ?? 'unknown',
                'line' => $entry['line'] ?? 'unknown',
                'function' => $entry['function'] ?? 'unknown',
                'class' => $entry['class'] ?? null,
                'type' => $entry['type'] ?? null,
                'args' => $this->formatArgs($entry['args'] ?? []),
            ];
        }

        return $formattedTrace;
    }

    private function formatArgs(array $args): array
    {
        return array_map(function ($arg) {
            if (is_object($arg)) {
                return sprintf('Object(%s)', get_class($arg));
            }
            if (is_array($arg)) {
                return sprintf('Array(%s)', count($arg));
            }
            if (is_string($arg)) {
                return sprintf("'%s'", $arg);
            }
            if (is_bool($arg)) {
                return $arg ? 'true' : 'false';
            }
            return var_export($arg, true);
        }, $args);
    }

    private function getPreviousExceptions(\Throwable $exception): array
    {
        $previousExceptions = [];
        $prev = $exception->getPrevious();

        while ($prev !== null) {
            $previousExceptions[] = [
                'class' => get_class($prev),
                'message' => $prev->getMessage(),
                'file' => $prev->getFile(),
                'line' => $prev->getLine(),
            ];
            $prev = $prev->getPrevious();
        }

        return $previousExceptions;
    }
}
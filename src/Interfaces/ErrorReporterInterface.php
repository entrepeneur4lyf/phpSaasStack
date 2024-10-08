<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface ErrorReporterInterface
{
    public function reportCriticalError(\Throwable $error): void;
    public function reportError(\Throwable $error): void;
    public function reportWarning(string $message, array $context = []): void;
    public function reportInfo(string $message, array $context = []): void;
}

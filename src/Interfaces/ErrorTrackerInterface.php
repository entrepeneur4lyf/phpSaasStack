<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface ErrorTrackerInterface
{
    public function trackError(\Throwable $error): void;
    public function getTopErrors(int $limit = 10): array;
}

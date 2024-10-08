<?php

declare(strict_types=1);

namespace Src\Services\Interfaces;

interface ThemeServiceInterface
{
    public function getTheme(): string;
    public function setTheme(string $theme): void;
}

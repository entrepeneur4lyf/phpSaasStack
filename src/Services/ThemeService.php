<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Services\Interfaces\ThemeServiceInterface;

final class ThemeService implements ThemeServiceInterface
{
    public function __construct(
        private readonly string $defaultTheme = 'dark'
    ) {
    }

    public function getTheme(): string
    {
        return $_SESSION['theme'] ?? $this->defaultTheme;
    }

    public function setTheme(string $theme): void
    {
        $_SESSION['theme'] = $theme;
    }
}

<?php

declare(strict_types=1);

namespace Src\Config;

class FeatureFlagManager
{
    private array $flags;

    public function __construct(array $flags)
    {
        $this->flags = $flags;
    }

    public function isEnabled(string $feature): bool
    {
        return $this->flags[$feature] ?? false;
    }

    public function getAllFlags(): array
    {
        return $this->flags;
    }

    public function setFlag(string $feature, bool $value): void
    {
        $this->flags[$feature] = $value;
    }
}

<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Config\Dependencies;

class ServiceRefresher
{
    private array $statefulServices = [
        'session',
        'database',
        'cache',
        // Add other stateful services here
    ];

    public function refreshStatefulServices(): void
    {
        foreach ($this->statefulServices as $serviceId) {
            Dependencies::refreshService($serviceId);
        }
    }

    public function refreshService(string $serviceId): void
    {
        if (in_array($serviceId, $this->statefulServices)) {
            Dependencies::refreshService($serviceId);
        } else {
            throw new \InvalidArgumentException("Service '{$serviceId}' is not a stateful service.");
        }
    }
}

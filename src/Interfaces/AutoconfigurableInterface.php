<?php

declare(strict_types=1);

namespace Src\Interfaces;

use Psr\Container\ContainerInterface;

interface AutoconfigurableInterface
{
    public function autoconfigure(ContainerInterface $container): void;
}
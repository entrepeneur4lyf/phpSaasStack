<?php

declare(strict_types=1);

namespace Src\Core;

use Src\Config\ConfigurationManager;
use Src\Interfaces\CacheInterface;
use Src\Services\ErrorReportingService;
use Src\Interfaces\ErrorReporterInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class Container
{
    // ... existing code ...

    protected function getDefaultBindings(): array
    {
        return [
            // ... existing bindings ...
            ErrorReporterInterface::class => function ($c) {
                return new ErrorReportingService(
                    $c->get(LoggerInterface::class),
                    $c->get(ConfigurationManager::class)
                );
            },
            ErrorHandler::class => function ($c) {
                $twig = $c->get(Environment::class);
                $errorReporter = $c->get(ErrorReporterInterface::class);
                $configManager = $c->get(ConfigurationManager::class);
                $debug = $configManager->get('app.debug', false);
                return new CustomErrorHandler($twig, $errorReporter, $debug);
            },
            // ... other bindings ...
        ];
    }
}
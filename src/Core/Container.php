<?php

declare(strict_types=1);

namespace Src\Core;

use Src\Services\ErrorReportingService;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Twig\Environment;

class Container
{
    // ... existing code ...

    protected function getDefaultBindings(): array
    {
        return [
            // ... existing bindings ...
            ErrorReportingService::class => function ($c) {
                return new ErrorReportingService(
                    $c->get(LoggerInterface::class),
                    $c->get(Config::class)
                );
            },
            ErrorHandler::class => function ($c) {
                $twig = $c->get(Environment::class);
                $errorReportingService = $c->get(ErrorReportingService::class);
                $debug = $c->get(Config::class)->get('app.debug', false);
                return new CustomErrorHandler($twig, $errorReportingService, $debug);
            },
            // ... other bindings ...
        ];
    }
}
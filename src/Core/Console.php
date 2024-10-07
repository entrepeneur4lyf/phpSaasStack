<?php

declare(strict_types=1);

namespace Src\Core;

use Symfony\Component\Console\Application;
use Src\Commands\GenerateDIConfigCommand;

class Console
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application();
        $this->registerCommands();
    }

    private function registerCommands(): void
    {
        $this->application->add(new GenerateDIConfigCommand());
        // Add other commands here
    }

    public function run(): void
    {
        $this->application->run();
    }
}
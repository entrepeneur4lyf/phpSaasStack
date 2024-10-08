<?php

declare(strict_types=1);

namespace Src\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Src\Config\ConfigurationManager;

class ReloadConfigCommand extends Command
{
    protected static $defaultName = 'config:reload';
    private ConfigurationManager $configManager;

    public function __construct(ConfigurationManager $configManager)
    {
        parent::__construct();
        $this->configManager = $configManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Reload the application configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configManager->reload();
        $output->writeln('Configuration reloaded successfully.');
        return Command::SUCCESS;
    }
}

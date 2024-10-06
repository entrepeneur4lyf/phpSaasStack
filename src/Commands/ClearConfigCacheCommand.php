<?php

declare(strict_types=1);

namespace Src\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Src\Config\ConfigurationManager;

class ClearConfigCacheCommand extends Command
{
    protected static $defaultName = 'config:clear-cache';
    private ConfigurationManager $configManager;
    private string $cacheDir;

    public function __construct(ConfigurationManager $configManager, string $cacheDir)
    {
        parent::__construct();
        $this->configManager = $configManager;
        $this->cacheDir = $cacheDir;
    }

    protected function configure(): void
    {
        $this->setDescription('Clear the configuration cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheFile = $this->cacheDir . '/config.php';
        
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            $output->writeln('Configuration cache cleared successfully.');
        } else {
            $output->writeln('No configuration cache file found.');
        }

        return Command::SUCCESS;
    }
}
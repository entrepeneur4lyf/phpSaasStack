<?php

declare(strict_types=1);

namespace Src\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Src\Services\ServiceRefresher;

class RefreshServicesCommand extends Command
{
    protected static $defaultName = 'services:refresh';

    private ServiceRefresher $serviceRefresher;

    public function __construct(ServiceRefresher $serviceRefresher)
    {
        parent::__construct();
        $this->serviceRefresher = $serviceRefresher;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Refresh stateful services')
            ->addOption('service', 's', InputOption::VALUE_OPTIONAL, 'Specific service to refresh');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serviceId = $input->getOption('service');

        if ($serviceId) {
            try {
                $this->serviceRefresher->refreshService($serviceId);
                $output->writeln("Service '{$serviceId}' refreshed successfully.");
            } catch (\InvalidArgumentException $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
                return Command::FAILURE;
            }
        } else {
            $this->serviceRefresher->refreshStatefulServices();
            $output->writeln('All stateful services refreshed successfully.');
        }

        return Command::SUCCESS;
    }
}
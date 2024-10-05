<?php

declare(strict_types=1);

namespace Src\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Src\Interfaces\AIServiceInterface;
use Src\Interfaces\RequestQueueServiceInterface;

class ProcessAIRequests extends Command
{
    protected static $defaultName = 'app:process-ai-requests';

    public function __construct(
        private readonly AIServiceInterface $aiService,
        private readonly RequestQueueServiceInterface $requestQueueService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Processes pending AI requests')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Number of requests to process', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int) $input->getOption('limit');
        $pendingRequests = $this->requestQueueService->getPendingRequests();
        $processedCount = 0;

        foreach ($pendingRequests as $request) {
            if ($processedCount >= $limit) {
                break;
            }

            try {
                $result = $this->aiService->processRequest($request);
                $this->requestQueueService->updateRequest($request['id'], 'completed', json_encode($result));
                $output->writeln("Processed request ID: {$request['id']}");
                $processedCount++;
            } catch (\Exception $e) {
                $this->requestQueueService->updateRequest($request['id'], 'error', $e->getMessage());
                $output->writeln("<error>Error processing request ID: {$request['id']} - {$e->getMessage()}</error>");
            }
        }

        $output->writeln("Processed $processedCount AI requests.");

        return Command::SUCCESS;
    }
}
<?php

declare(strict_types=1);

namespace Src\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Src\Interfaces\PostServiceInterface;

class PublishScheduledPosts extends Command
{
    protected static $defaultName = 'app:publish-scheduled-posts';

    public function __construct(
        private readonly PostServiceInterface $postService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Publishes all scheduled posts that are due');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $publishedCount = $this->postService->publishScheduledPosts();

        if ($publishedCount > 0) {
            $output->writeln("Published {$publishedCount} scheduled posts.");
        } else {
            $output->writeln("No scheduled posts to publish.");
        }

        return Command::SUCCESS;
    }
}

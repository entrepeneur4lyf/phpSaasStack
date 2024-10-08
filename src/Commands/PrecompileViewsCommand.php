<?php

declare(strict_types=1);

namespace Src\Commands;

use Src\Core\TwigRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrecompileViewsCommand extends Command
{
    protected static $defaultName = 'views:precompile';
    private TwigRenderer $twigRenderer;

    public function __construct(TwigRenderer $twigRenderer)
    {
        parent::__construct();
        $this->twigRenderer = $twigRenderer;
    }

    protected function configure(): void
    {
        $this->setDescription('Pre-compile all Twig views for production use');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting view pre-compilation...');

        try {
            $this->twigRenderer->precompileViews();
            $output->writeln('All views have been pre-compiled successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>An error occurred during view pre-compilation:</error>');
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}

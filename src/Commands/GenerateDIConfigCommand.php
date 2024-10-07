<?php

declare(strict_types=1);

namespace Src\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

class GenerateDIConfigCommand extends Command
{
    protected static $defaultName = 'app:generate-di-config';

    protected function configure()
    {
        $this->setDescription('Generates dependency injection configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating Dependency Injection Configuration');

        $services = $this->discoverServices();
        $config = $this->generateConfig($services);

        $this->writeConfig($config);

        $io->success('Dependency injection configuration has been generated.');

        return Command::SUCCESS;
    }

    private function discoverServices(): array
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../')->name('*.php');

        $services = [];

        foreach ($finder as $file) {
            $className = $this->getFullyQualifiedClassName($file);
            if ($className) {
                $services[] = $className;
            }
        }

        return $services;
    }

    private function getFullyQualifiedClassName($file): ?string
    {
        $contents = $file->getContents();
        if (preg_match('/namespace\s+(.+);/', $contents, $matches)) {
            $namespace = $matches[1];
            if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
                return $namespace . '\\' . $matches[1];
            }
        }
        return null;
    }

    private function generateConfig(array $services): array
    {
        $config = [
            'services' => [
                '_defaults' => [
                    'autowire' => true,
                    'autoconfigure' => true,
                    'public' => false,
                ],
            ],
        ];

        foreach ($services as $service) {
            $serviceConfig = ['class' => $service];
            
            // Add tags based on implemented interfaces
            $reflection = new \ReflectionClass($service);
            if ($reflection->implementsInterface(\Src\Interfaces\CommandInterface::class)) {
                $serviceConfig['tags'][] = 'command';
            }
            // Add more tag rules as needed

            $config['services'][$service] = $serviceConfig;
        }

        return $config;
    }

    private function writeConfig(array $config): void
    {
        $yaml = Yaml::dump($config, 4, 2);
        file_put_contents(__DIR__ . '/../Config/generated_services.yaml', $yaml);
    }
}
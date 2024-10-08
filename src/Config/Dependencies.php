<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\LazyProxy\Instantiator\RealServiceInstantiator;
use Symfony\Component\DependencyInjection\Definition;

class Dependencies
{
    private const CONTAINER_CACHE_FILE = __DIR__ . '/../../var/cache/container.php';
    private static $container;
    private static $requestScopedServices = [];

    public static function getContainer(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        if (self::$container === null) {
            $containerConfigCache = new ConfigCache(self::CONTAINER_CACHE_FILE, $_ENV['APP_DEBUG'] ?? false);

            if (!$containerConfigCache->isFresh()) {
                $containerBuilder = new ContainerBuilder();
                self::loadServices($containerBuilder);
                self::optimizeContainer($containerBuilder);

                $dumper = new PhpDumper($containerBuilder);
                $containerConfigCache->write(
                    $dumper->dump([
                        'class' => 'CachedContainer',
                        'base_class' => 'Symfony\Component\DependencyInjection\Container',
                        'file' => self::CONTAINER_CACHE_FILE,
                    ]),
                    $containerBuilder->getResources()
                );
            }

            require_once self::CONTAINER_CACHE_FILE;
            self::$container = new \CachedContainer();
        }

        return self::$container;
    }

    public static function refreshService(string $serviceId): void
    {
        $container = self::getContainer();
        if ($container->has($serviceId)) {
            $container->set($serviceId, null);
            $container->get($serviceId);
        }
    }

    public static function refreshService(string $serviceId): void
    {
        $container = self::getContainer();
        if ($container->has($serviceId)) {
            $container->set($serviceId, null);
            $container->get($serviceId);
        }
    }

    public static function getRequestScopedService(string $serviceId)
    {
        if (!isset(self::$requestScopedServices[$serviceId])) {
            self::$requestScopedServices[$serviceId] = self::getContainer()->get($serviceId);
        }
        return self::$requestScopedServices[$serviceId];
    }

    public static function clearRequestScopedServices(): void
    {
        self::$requestScopedServices = [];
    }

    private static function loadServices(ContainerBuilder $containerBuilder): void
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('services.yaml');

        // Convert string log levels to Monolog constants
        $streamHandler = $containerBuilder->getDefinition('Monolog\Handler\StreamHandler');
        $arguments = $streamHandler->getArguments();
        if (isset($arguments[1]) && is_string($arguments[1])) {
            $logLevel = constant('Monolog\Logger::' . strtoupper($arguments[1]));
            $streamHandler->replaceArgument(1, $logLevel);
        }

        // Set scopes for services
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            $scope = $definition->getTag('scope')[0] ?? 'singleton';
            self::setScopeForDefinition($definition, $scope);
        }
    }

    private static function setScopeForDefinition(Definition $definition, string $scope): void
    {
        switch ($scope) {
            case 'request':
                $definition->setShared(false);
                break;
            case 'transient':
                $definition->setShared(false);
                break;
            case 'singleton':
            default:
                $definition->setShared(true);
                break;
        }
    }

    private static function optimizeContainer(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setProxyInstantiator(new RealServiceInstantiator());
        $containerBuilder->getCompiler()->addPass(new OptimizeServicesPass());
        $containerBuilder->compile();
    }
}

class OptimizeServicesPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            // Make services lazy loadable
            if (!$this->isStatefulService($id)) {
                $definition->setLazy(true);
            }

            // Use factories for stateful services
            if ($this->isStatefulService($id)) {
                $definition->setShared(false);
                $definition->setFactory([new Reference('service_factory'), 'create']);
            }
        }
    }

    private function isStatefulService(string $id): bool
    {
        // Define your logic to determine if a service is stateful
        $statefulServices = ['session', 'database', 'cache'];
        return in_array($id, $statefulServices);
    }
}

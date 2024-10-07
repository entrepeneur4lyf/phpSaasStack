<?php

declare(strict_types=1);

namespace Src\Core;

use Src\Config\ConfigurationManager;
use Src\Interfaces\CacheInterface;
use Src\Services\ErrorReportingService;
use Src\Interfaces\ErrorReporterInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Twig\Environment;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $instances = [];
    private array $definitions = [];
    private ServiceFactory $factory;
    private array $tags = [];

    public function __construct()
    {
        $this->factory = new ServiceFactory($this);
    }

    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function make(string $abstract, array $parameters = [])
    {
        return $this->factory->create($abstract, $parameters);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    protected function getDefaultBindings(): array
    {
        return [
            ConfigurationManager::class => function ($c) {
                return new ConfigurationManager();
            },
            CacheInterface::class => function ($c) {
                $config = $c->make(ConfigurationManager::class)->get('cache');
                return new $config['default']['driver']($config['default']);
            },
            LoggerInterface::class => function ($c) {
                $config = $c->make(ConfigurationManager::class)->get('logging');
                $logger = new \Monolog\Logger($config['channel']);
                $logger->pushHandler(new \Monolog\Handler\StreamHandler(
                    $config['path'],
                    constant('\Monolog\Logger::' . strtoupper($config['level']))
                ));
                return $logger;
            },
            ErrorReporterInterface::class => function ($c) {
                return new ErrorReportingService(
                    $c->make(LoggerInterface::class),
                    $c->make(ConfigurationManager::class)
                );
            },
            ErrorHandler::class => function ($c) {
                $twig = $c->make(Environment::class);
                $errorReporter = $c->make(ErrorReporterInterface::class);
                $configManager = $c->make(ConfigurationManager::class);
                $debug = $configManager->get('app.debug', false);
                return new CustomErrorHandler($twig, $errorReporter, $debug);
            },
            Environment::class => function ($c) {
                $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../Views');
                return new Environment($loader, [
                    'cache' => __DIR__ . '/../../var/cache/twig',
                    'debug' => $c->make(ConfigurationManager::class)->get('app.debug', false),
                ]);
            },
            JWTServiceInterface::class => function ($c) {
                $config = $c->make(ConfigurationManager::class)->get('jwt');
                return new JWTService($config['secret'], $config['expiration']);
            },
            UserServiceInterface::class => function ($c) {
                return new UserService($c->make(DatabaseService::class));
            },
            AuthServiceInterface::class => function ($c) {
                return new AuthService(
                    $c->make(UserServiceInterface::class),
                    $c->make(JWTServiceInterface::class)
                );
            },
            DatabaseService::class => function ($c) {
                $config = $c->make(ConfigurationManager::class)->get('database');
                return new DatabaseService($config);
            },
            OpenAIWrapperInterface::class => function ($c) {
                $config = $c->make(ConfigurationManager::class)->get('openai');
                return new OpenAIWrapper($config['api_key']);
            },
            RequestQueueServiceInterface::class => function ($c) {
                return new RequestQueueService($c->make(DatabaseService::class));
            },
            AIServiceInterface::class => function ($c) {
                return new AIService(
                    $c->make(OpenAIWrapperInterface::class),
                    $c->make(RequestQueueServiceInterface::class)
                );
            },
            FeatureFlagManager::class => function ($c) {
                $config = $c->make(ConfigurationManager::class)->get('feature_flags');
                return new FeatureFlagManager($config);
            },
            Router::class => function ($c) {
                return new Router($c);
            },
            'service_factory' => function ($c) {
                return new ServiceFactory($c);
            },
            TwigRenderer::class => function ($c) {
                $configManager = $c->make(ConfigurationManager::class);
                $templatePath = $configManager->get('app.views_path', __DIR__ . '/../Views');
                return new TwigRenderer($templatePath);
            },
            BaseController::class => function ($c) {
                return new BaseController($c->make(TwigRenderer::class));
            },
        ];
    }

    public function loadDefaultBindings(): void
    {
        foreach ($this->getDefaultBindings() as $abstract => $concrete) {
            $this->bind($abstract, $concrete);
        }
    }

    public function flush(): void
    {
        $this->instances = [];
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \Exception("Service not found: $id");
        }

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->resolve($id);
        }

        return $this->instances[$id];
    }

    public function has($id): bool
    {
        return isset($this->definitions[$id]) || isset($this->instances[$id]);
    }

    public function set($id, $concrete): void
    {
        $this->definitions[$id] = $concrete;
        $this->autoconfigure($id);
    }

    private function resolve($id)
    {
        $concrete = $this->definitions[$id];

        if (is_callable($concrete)) {
            return $concrete($this);
        }

        if (is_string($concrete)) {
            return $this->factory->create($concrete);
        }

        return $concrete;
    }

    public function tag(string $tagName, string $serviceId): void
    {
        if (!isset($this->tags[$tagName])) {
            $this->tags[$tagName] = [];
        }
        $this->tags[$tagName][] = $serviceId;
    }

    public function getTaggedServices(string $tagName): array
    {
        return $this->tags[$tagName] ?? [];
    }

    public function autoconfigure(string $serviceId): void
    {
        $reflection = new \ReflectionClass($serviceId);
        
        // Example: Automatically tag services implementing specific interfaces
        if ($reflection->implementsInterface(\Src\Interfaces\CommandInterface::class)) {
            $this->tag('command', $serviceId);
        }
        
        // Add more autoconfiguration rules as needed
    }
}
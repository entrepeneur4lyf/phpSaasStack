<?php

declare(strict_types=1);

namespace Src\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Src\Exceptions\ConfigurationException;

class ConfigurationManager
{
    private array $config = [];
    private string $cacheDir;
    private bool $debug;
    private FeatureFlagManager $featureFlagManager;

    public function __construct(string $configDir, string $cacheDir, bool $debug)
    {
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;

        $configCache = new ConfigCache($cacheDir . '/config.php', $debug);

        if (!$configCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();
            $loader = $this->getLoader($containerBuilder, $configDir);
            $loader->load('config.php');

            $this->config = $containerBuilder->getParameterBag()->all();

            // Add .env variables to the configuration
            foreach ($_ENV as $key => $value) {
                $this->config[$key] = $value;
            }

            $configCache->write(
                '<?php return ' . var_export($this->config, true) . ';',
                [new FileResource($configDir . '/config.php')]
            );
        } else {
            $this->config = require $configCache->getPath();
        }

        $this->validateCriticalConfig();
        $this->initializeFeatureFlags();
    }

    private function getLoader(ContainerBuilder $container, string $configDir): LoaderInterface
    {
        $locator = new FileLocator($configDir);
        $loaderResolver = new LoaderResolver([
            new PhpFileLoader($container, $locator),
        ]);
        return new DelegatingLoader($loaderResolver);
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function reload(): void
    {
        $configCache = new ConfigCache($this->cacheDir . '/config.php', $this->debug);
        $configCache->write(
            '<?php return ' . var_export($this->config, true) . ';',
            [new FileResource(__DIR__ . '/config.php')]
        );
    }

    public function validateCriticalConfig(): void
    {
        $criticalKeys = [
            'APP_ENV',
            'APP_DEBUG',
            'APP_KEY',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'REDIS_HOST',
            'REDIS_PORT',
            'SWOOLE_HOST',
            'SWOOLE_PORT',
        ];

        $missingKeys = [];

        foreach ($criticalKeys as $key) {
            if (!isset($this->config[$key]) || empty($this->config[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            throw new ConfigurationException('Missing critical configuration values: ' . implode(', ', $missingKeys));
        }

        // Additional specific validations
        if (!in_array($this->config['APP_ENV'], ['production', 'development', 'testing'])) {
            throw new ConfigurationException('Invalid APP_ENV value. Must be one of: production, development, testing');
        }

        if (!is_bool($this->config['APP_DEBUG'])) {
            throw new ConfigurationException('APP_DEBUG must be a boolean value');
        }

        if (strlen($this->config['APP_KEY']) < 32) {
            throw new ConfigurationException('APP_KEY must be at least 32 characters long');
        }
    }

    private function initializeFeatureFlags(): void
    {
        $featureFlags = $this->config['feature_flags'] ?? [];
        $this->featureFlagManager = new FeatureFlagManager($featureFlags);
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return $this->featureFlagManager->isEnabled($feature);
    }

    public function setFeatureFlag(string $feature, bool $value): void
    {
        $this->featureFlagManager->setFlag($feature, $value);
        $this->config['feature_flags'][$feature] = $value;
        $this->reload();
    }

    public function getAllFeatureFlags(): array
    {
        return $this->featureFlagManager->getAllFlags();
    }
}

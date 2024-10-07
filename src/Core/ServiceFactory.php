<?php

declare(strict_types=1);

namespace Src\Core;

use Psr\Container\ContainerInterface;

class ServiceFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $className, array $arguments = [])
    {
        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->isInstantiable()) {
            throw new \InvalidArgumentException("Class $className is not instantiable");
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            $instance = new $className();
        } else {
            $params = $this->resolveConstructorParameters($constructor, $arguments);
            $instance = $reflectionClass->newInstanceArgs($params);
        }

        $this->autoconfigure($instance);

        return $instance;
    }

    private function resolveConstructorParameters(\ReflectionMethod $constructor, array $arguments): array
    {
        $params = [];

        foreach ($constructor->getParameters() as $param) {
            if (isset($arguments[$param->getName()])) {
                $params[] = $arguments[$param->getName()];
            } elseif ($param->getType() && !$param->getType()->isBuiltin()) {
                $typeName = $param->getType()->getName();
                if ($this->container->has($typeName)) {
                    $params[] = $this->container->get($typeName);
                } else {
                    $params[] = $this->create($typeName);
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new \InvalidArgumentException("Unable to resolve parameter: " . $param->getName());
            }
        }

        return $params;
    }

    private function autoconfigure($instance): void
    {
        if ($instance instanceof \Src\Interfaces\AutoconfigurableInterface) {
            $instance->autoconfigure($this->container);
        }
    }
}
<?php

declare(strict_types=1);

namespace Nexus\Container;

use Nexus\Exception\ContainerException;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    /** @var array<string, callable(self): mixed> */
    private array $factories = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $resolving = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (isset($this->resolving[$id])) {
            throw new ContainerException("Circular dependency detected: {$id}");
        }

        if (!isset($this->factories[$id])) {
            if (class_exists($id)) {
                return $this->instances[$id] = $this->autowire($id);
            }
            throw new ContainerException("Service not found: {$id}");
        }

        $this->resolving[$id] = true;
        try {
            $instance = ($this->factories[$id])($this);
        } finally {
            unset($this->resolving[$id]);
        }

        return $this->instances[$id] = $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id])
            || array_key_exists($id, $this->instances)
            || class_exists($id);
    }

    private function autowire(string $class): object
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Not instantiable: {$class}");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }
                throw new ContainerException("Cannot resolve parameter \${$param->getName()} for {$class}");
            }
            $args[] = $this->get($type->getName());
        }

        return $reflection->newInstanceArgs($args);
    }
}
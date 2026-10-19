<?php

declare(strict_types=1);

namespace Nexus\Bootstrap;

use Nexus\Exception\KernelException;
use Nexus\Kernel\KernelContext;

final class ProviderLoader
{
    /** @return ServiceProviderInterface[] */
    public static function load(KernelContext $context): array
    {
        $providers = $context->config->get('providers', []);
        if (!is_array($providers)) {
            return [];
        }

        $instances = [];
        foreach ($providers as $class) {
            if (!is_string($class) || !class_exists($class)) {
                throw new KernelException("Provider not found: " . (is_string($class) ? $class : get_debug_type($class)));
            }
            $instance = $context->container->get($class);
            if (!$instance instanceof ServiceProviderInterface) {
                throw new KernelException("Provider must implement ServiceProviderInterface: {$class}");
            }
            $instances[] = $instance;
        }

        return $instances;
    }

    public static function registerAll(KernelContext $context, array $providers): void
    {
        foreach ($providers as $provider) {
            $provider->register($context);
        }
    }

    public static function bootAll(KernelContext $context, array $providers): void
    {
        foreach ($providers as $provider) {
            $provider->boot($context);
        }
    }
}
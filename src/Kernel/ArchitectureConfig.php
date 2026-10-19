<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Config\Config;

final class ArchitectureConfig
{
    public function __construct(
        public readonly string $default,
        public readonly array $routes,
        public readonly array $modules,
        public readonly array $compose,
        public readonly string $strategy,
    ) {
    }

    public static function fromConfig(Config $config): self
    {
        $default = (string) $config->get('architecture.default', 'mvc');
        $routes = $config->get('architecture.routes', []);
        $modules = $config->get('architecture.modules', []);
        $compose = $config->get('architecture.compose', []);
        $strategy = (string) $config->get('architecture.strategy', 'fallthrough');

        return new self(
            default: $default,
            routes: is_array($routes) ? $routes : [],
            modules: is_array($modules) ? $modules : [],
            compose: is_array($compose) ? $compose : [],
            strategy: $strategy,
        );
    }
}
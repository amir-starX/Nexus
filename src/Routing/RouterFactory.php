<?php

declare(strict_types=1);

namespace Nexus\Routing;

use Nexus\Config\Config;
use Nexus\Container\Container;

final class RouterFactory
{
    public static function build(Config $config, Container $container): Router
    {
        $router = new Router();

        $routes = $config->get('routing.routes', []);
        if (!is_array($routes)) {
            return $router;
        }

        foreach ($routes as $path => $definition) {
            if (!is_array($definition)) {
                continue;
            }
            $methods = (array) ($definition['methods'] ?? ['GET']);
            $methods = array_map('strtoupper', $methods);
            $handler = $definition['handler'] ?? null;
            $architecture = $definition['architecture'] ?? null;
            $module = $definition['module'] ?? null;

            $router->add(new Route(
                path: $path,
                methods: $methods,
                pattern: $path,
                handler: $handler,
                architecture: is_string($architecture) ? $architecture : null,
                module: is_string($module) ? $module : null,
            ));
        }

        return $router;
    }
}
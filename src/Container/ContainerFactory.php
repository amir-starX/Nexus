<?php

declare(strict_types=1);

namespace Nexus\Container;

use Nexus\Config\Config;

final class ContainerFactory
{
    public static function build(Config $config, string $basePath): Container
    {
        $container = new Container();
        $container->instance(Container::class, $container);
        $container->instance(Config::class, $config);

        $bindings = $config->get('container.bindings', []);
        if (is_array($bindings)) {
            foreach ($bindings as $id => $factory) {
                if (is_string($factory) && class_exists($factory)) {
                    $container->set($id, static fn (Container $c) => $c->get($factory));
                } elseif (is_callable($factory)) {
                    $container->set($id, $factory);
                }
            }
        }

        return $container;
    }
}
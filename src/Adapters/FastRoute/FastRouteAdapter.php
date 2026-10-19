<?php

declare(strict_types=1);

namespace Nexus\Adapters\FastRoute;

use FastRoute\RouteCollector;
use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

use function FastRoute\simpleDispatcher;

final class FastRouteAdapter extends Adapter
{
    public function name(): string
    {
        return 'fastroute';
    }

    public function boot(KernelContext $context): void
    {
        if (!function_exists('FastRoute\\simpleDispatcher')) {
            return;
        }

        $routes = $context->router->all();

        $dispatcher = simpleDispatcher(function (RouteCollector $collector) use ($routes): void {
            foreach ($routes as $route) {
                $collector->addRoute($route->methods, $route->path, $route->handler);
            }
        });

        $context->container->instance('fastroute.dispatcher', $dispatcher);
    }
}
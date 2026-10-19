<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Contracts\PatternResolverInterface;
use Nexus\Http\Request;
use Nexus\Routing\Route;

final class DefaultPatternResolver implements PatternResolverInterface
{
    public function __construct(private readonly ArchitectureConfig $config)
    {
    }

    public function resolve(Request $request, KernelContext $context): string
    {
        $route = $request->attributes['route'] ?? null;
        if ($route instanceof Route) {
            if ($route->architecture !== null && $route->architecture !== '') {
                return $route->architecture;
            }
            if ($route->module !== null && isset($this->config->modules[$route->module])) {
                return (string) $this->config->modules[$route->module];
            }
        }

        $routeKey = $request->uri;
        if (isset($this->config->routes[$routeKey])) {
            return (string) $this->config->routes[$routeKey];
        }

        return $this->config->default;
    }

    public function chain(Request $request, KernelContext $context): array
    {
        if ($this->config->compose !== []) {
            return array_values(array_map('strval', $this->config->compose));
        }

        return [$this->resolve($request, $context)];
    }
}
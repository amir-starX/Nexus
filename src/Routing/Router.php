<?php

declare(strict_types=1);

namespace Nexus\Routing;

use Nexus\Exception\RoutingException;
use Nexus\Http\Request;

final class Router
{
    /** @var Route[] */
    private array $routes = [];

    public function add(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function matchRoute(string $uri, string $method): Route
    {
        foreach ($this->routes as $route) {
            if (!in_array($method, $route->methods, true) && !in_array('*', $route->methods, true)) {
                continue;
            }
            $params = $this->matchPath($route->pattern, $uri);
            if ($params === null) {
                continue;
            }
            $matched = new Route(
                path: $route->path,
                methods: $route->methods,
                pattern: $route->pattern,
                handler: $route->handler,
                architecture: $route->architecture,
                module: $route->module,
            );
            $matched->params = $params;
            return $matched;
        }

        throw new RoutingException("No route matched: {$method} {$uri}");
    }

    public function match(string $uri, string $method): Request
    {
        $route = $this->matchRoute($uri, $method);
        $request = new Request(
            uri: $uri,
            method: $method,
            headers: [],
            query: $_GET,
            body: $_POST,
            server: $_SERVER,
        );
        $request->attributes['route'] = $route;
        $request->attributes['params'] = $route->params ?? [];
        return $request;
    }

    /** @return Route[] */
    public function all(): array
    {
        return $this->routes;
    }

    private function matchPath(string $pattern, string $uri): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . rtrim((string) $regex, '/') . '/?$#';

        if (!preg_match($regex, $uri, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
}
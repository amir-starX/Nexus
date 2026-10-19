<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Contracts\CompositeKernelInterface;
use Nexus\Http\Request;
use Nexus\Http\Response;

final class Dispatcher
{
    public function __construct(private readonly CompositeKernelInterface $kernel)
    {
    }

    public function dispatch(string $uri, string $method, KernelContext $context): Response
    {
        $request = Request::capture();
        $route = $context->router->matchRoute($request->uri, $method);

        $request->attributes['route'] = $route;
        $request->attributes['params'] = $route->params ?? [];

        return $this->kernel->handle($context, $request);
    }
}
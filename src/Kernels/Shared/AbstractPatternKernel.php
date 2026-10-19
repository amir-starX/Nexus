<?php

declare(strict_types=1);

namespace Nexus\Kernels\Shared;

use Nexus\Contracts\PatternKernelInterface;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;
use Nexus\Routing\Route;

abstract class AbstractPatternKernel implements PatternKernelInterface
{
    public function supports(RequestContext $ctx): bool
    {
        return true;
    }

    protected function route(RequestContext $ctx): ?Route
    {
        $route = $ctx->request->attributes['route'] ?? null;
        return $route instanceof Route ? $route : null;
    }

    protected function params(RequestContext $ctx): array
    {
        $params = $ctx->request->attributes['params'] ?? [];
        return is_array($params) ? $params : [];
    }

    protected function invokeHandler(KernelContext $context, RequestContext $ctx): Response
    {
        $route = $this->route($ctx);
        if ($route === null) {
            return Response::notFound('No route context');
        }
        return $context->handlerResolver->invoke($route->handler, $context, $ctx->request);
    }
}
<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Container\Container;
use Nexus\Contracts\HandlerResolverInterface;
use Nexus\Exception\KernelException;
use Nexus\Http\Request;
use Nexus\Http\Response;

final class HandlerResolver implements HandlerResolverInterface
{
    public function __construct(private readonly Container $container)
    {
    }

    public function invoke(mixed $handler, KernelContext $context, Request $request): Response
    {
        if (is_callable($handler)) {
            $result = $handler($request, $context);
            return $this->normalize($result);
        }

        if (!is_string($handler)) {
            throw new KernelException('Invalid handler type: ' . get_debug_type($handler));
        }

        [$class, $method] = $this->parse($handler);
        if (!$this->container->has($class)) {
            throw new KernelException("Handler class not found: {$class}");
        }

        $instance = $this->container->get($class);
        if (!method_exists($instance, $method)) {
            throw new KernelException("Handler method not found: {$class}::{$method}");
        }

        $result = $instance->{$method}($request, $context);
        return $this->normalize($result);
    }

    /** @return array{0: string, 1: string} */
    private function parse(string $handler): array
    {
        if (!str_contains($handler, '@')) {
            return [$handler, '__invoke'];
        }
        return explode('@', $handler, 2);
    }

    private function normalize(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
        if (is_array($result) || is_object($result)) {
            return Response::json($result);
        }
        return Response::text((string) $result);
    }
}
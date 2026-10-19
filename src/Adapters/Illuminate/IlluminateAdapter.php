<?php

declare(strict_types=1);

namespace Nexus\Adapters\Illuminate;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class IlluminateAdapter extends Adapter
{
    public function name(): string
    {
        return 'illuminate';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(\Illuminate\Container\Container::class)) {
            return;
        }

        $container = new \Illuminate\Container\Container();
        $container->instance(\Illuminate\Contracts\Container\Container::class, $container);
        $container->instance(\Nexus\Container\Container::class, $context->container);

        $context->container->instance('illuminate.container', $container);
    }
}
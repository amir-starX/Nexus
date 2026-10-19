<?php

declare(strict_types=1);

namespace Nexus\Adapters\League;

use League\Container\Container;
use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;
use Psr\Container\ContainerInterface;

final class LeagueAdapter extends Adapter
{
    public function name(): string
    {
        return 'league';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(Container::class)) {
            return;
        }

        $container = new Container();
        $container->delegate(new \League\Container\ReflectionContainer());

        $context->container->instance('league.container', $container);
        $context->container->instance(ContainerInterface::class, $container);
    }
}
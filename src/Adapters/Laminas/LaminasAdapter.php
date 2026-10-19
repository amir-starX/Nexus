<?php

declare(strict_types=1);

namespace Nexus\Adapters;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class LaminasAdapter extends Adapter
{
    public function name(): string
    {
        return 'laminas';
    }

    public function boot(KernelContext $context): void
    {
        if (class_exists(\Laminas\ServiceManager\ServiceManager::class)) {
            $manager = new \Laminas\ServiceManager\ServiceManager();
            $context->container->instance('laminas.service_manager', $manager);
        }

        if (class_exists(\Laminas\Diactoros\ServerRequestFactory::class)) {
            $request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
            $context->container->instance('laminas.request', $request);
        }
    }
}
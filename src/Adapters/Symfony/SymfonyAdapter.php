<?php

declare(strict_types=1);

namespace Nexus\Adapters;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class SymfonyAdapter extends Adapter
{
    public function name(): string
    {
        return 'symfony';
    }

    public function boot(KernelContext $context): void
    {
        if (class_exists(\Symfony\Component\EventDispatcher\EventDispatcher::class)) {
            $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
            $context->container->instance('symfony.event_dispatcher', $dispatcher);
        }

        if (class_exists(\Symfony\Component\HttpFoundation\Request::class)) {
            $symfonyRequest = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $context->container->instance('symfony.request', $symfonyRequest);
        }
    }
}
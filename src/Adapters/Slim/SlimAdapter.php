<?php

declare(strict_types=1);

namespace Nexus\Adapters;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class SlimAdapter extends Adapter
{
    public function name(): string
    {
        return 'slim';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(\Slim\Factory\AppFactory::class)) {
            return;
        }

        $app = \Slim\Factory\AppFactory::create();
        $context->container->instance('slim.app', $app);
    }
}
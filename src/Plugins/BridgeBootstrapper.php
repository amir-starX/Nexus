<?php

declare(strict_types=1);

namespace Nexus\Plugins;

use Nexus\Kernel\KernelContext;

final class BridgeBootstrapper
{
    public static function boot(KernelContext $context): BridgeManager
    {
        $manager = new BridgeManager($context);
        $context->container->instance(BridgeManager::class, $manager);
        $manager->boot();

        return $manager;
    }
}
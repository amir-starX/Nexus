<?php

declare(strict_types=1);

namespace Nexus\Plugins\Bridge;

use Nexus\Contracts\PluginBridgeInterface;
use Nexus\Kernel\KernelContext;

final class ExampleBridge implements PluginBridgeInterface
{
    public function name(): string
    {
        return 'example';
    }

    public function requires(): array
    {
        return [];
    }

    public function register(KernelContext $context): void
    {
        $context->container->instance('plugin.example', new \stdClass());
    }
}
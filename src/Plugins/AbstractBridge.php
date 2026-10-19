<?php

declare(strict_types=1);

namespace Nexus\Plugins;

use Nexus\Contracts\PluginBridgeInterface;

abstract class AbstractBridge implements PluginBridgeInterface
{
    public function requires(): array
    {
        return [];
    }

    protected function bind(string $id, callable $factory): void
    {
    }
}
<?php

declare(strict_types=1);

namespace Nexus\Kernels\Microkernel;

use Nexus\Exception\KernelException;

final class PluginRegistry
{
    /** @var array<string, PluginInterface> */
    private array $plugins = [];

    public function register(PluginInterface $plugin): void
    {
        $this->plugins[$plugin->name()] = $plugin;
    }

    public function get(string $name): PluginInterface
    {
        return $this->plugins[$name]
            ?? throw new KernelException("Plugin not registered: {$name}");
    }

    /** @return array<string, PluginInterface> */
    public function all(): array
    {
        return $this->plugins;
    }
}
<?php

declare(strict_types=1);

namespace Nexus\Contracts;

use Nexus\Kernel\KernelContext;

interface PluginBridgeInterface
{
    public function name(): string;

    /** @return string[] composer package requirements */
    public function requires(): array;

    public function register(KernelContext $context): void;
}
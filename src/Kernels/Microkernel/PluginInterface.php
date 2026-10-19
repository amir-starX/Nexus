<?php

declare(strict_types=1);

namespace Nexus\Kernels\Microkernel;

use Nexus\Kernel\KernelContext;

interface PluginInterface
{
    public function name(): string;

    public function boot(KernelContext $context): void;
}
<?php

declare(strict_types=1);

namespace Nexus\Contracts;

use Nexus\Kernel\KernelContext;

interface AdapterInterface
{
    public function name(): string;

    public function boot(KernelContext $context): void;
}
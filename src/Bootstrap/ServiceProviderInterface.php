<?php

declare(strict_types=1);

namespace Nexus\Bootstrap;

use Nexus\Kernel\KernelContext;

interface ServiceProviderInterface
{
    public function register(KernelContext $context): void;

    public function boot(KernelContext $context): void;
}
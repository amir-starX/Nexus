<?php

declare(strict_types=1);

namespace Nexus\Contracts;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;

interface CompositeKernelInterface
{
    public function handle(KernelContext $context, Request $request): Response;
}
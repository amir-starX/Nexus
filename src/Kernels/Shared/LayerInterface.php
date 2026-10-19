<?php

declare(strict_types=1);

namespace Nexus\Kernels\Shared;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;

interface LayerInterface
{
    public function name(): string;

    public function pass(Request $request, KernelContext $context, callable $next): Response;
}
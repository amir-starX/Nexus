<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Http\Request;

interface PatternResolver
{
    public function resolve(Request $request, KernelContext $context): string;

    public function chain(Request $request, KernelContext $context): array;
}
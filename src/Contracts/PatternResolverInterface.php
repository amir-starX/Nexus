<?php

declare(strict_types=1);

namespace Nexus\Contracts;

use Nexus\Http\Request;
use Nexus\Kernel\KernelContext;

interface PatternResolverInterface
{
    public function resolve(Request $request, KernelContext $context): string;

    /** @return string[] */
    public function chain(Request $request, KernelContext $context): array;
}
<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Http\Response;

interface PatternKernel
{
    public function name(): string;

    public function handle(KernelContext $context, RequestContext $request): Response;
}
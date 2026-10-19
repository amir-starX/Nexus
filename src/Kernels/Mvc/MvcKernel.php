<?php

declare(strict_types=1);

namespace Nexus\Kernels\Mvc;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class MvcKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'mvc';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        return $this->invokeHandler($context, $ctx);
    }
}
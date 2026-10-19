<?php

declare(strict_types=1);

namespace Nexus\Kernels\Adr;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class AdrKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'adr';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        return $this->invokeHandler($context, $ctx);
    }
}
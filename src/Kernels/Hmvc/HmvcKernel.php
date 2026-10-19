<?php

declare(strict_types=1);

namespace Nexus\Kernels\Hmvc;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class HmvcKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'hmvc';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        $module = $ctx->request->attributes['route']?->module;
        if ($module !== null) {
            $ctx->request->attributes['hmvc.module'] = $module;
        }
        return $this->invokeHandler($context, $ctx);
    }
}
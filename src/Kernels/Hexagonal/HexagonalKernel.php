<?php

declare(strict_types=1);

namespace Nexus\Kernels\Hexagonal;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class HexagonalKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'hexagonal';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        $ports = $context->config->get('architecture.hexagonal.ports', []);
        if (is_array($ports)) {
            foreach ($ports as $name => $class) {
                if (!is_string($class) || !class_exists($class)) {
                    continue;
                }
                $instance = $context->container->get($class);
                if ($instance instanceof Port) {
                    $ctx->request->attributes['ports'][$name] = $instance;
                }
            }
        }
        return $this->invokeHandler($context, $ctx);
    }
}
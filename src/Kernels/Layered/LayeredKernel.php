<?php

declare(strict_types=1);

namespace Nexus\Kernels\Layered;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Kernels\Shared\LayerInterface;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class LayeredKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'layered';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        $layers = $context->config->get('architecture.layered.layers', []);
        $instances = [];

        if (is_array($layers)) {
            foreach ($layers as $class) {
                if (!is_string($class) || !class_exists($class)) {
                    continue;
                }
                $instance = $context->container->get($class);
                if ($instance instanceof LayerInterface) {
                    $instances[] = $instance;
                }
            }
        }

        $pipeline = array_reduce(
            array_reverse($instances),
            fn (callable $next, LayerInterface $layer) => fn ($req) => $layer->pass($req, $context, $next),
            fn () => $this->invokeHandler($context, $ctx),
        );

        return $pipeline($ctx->request);
    }
}
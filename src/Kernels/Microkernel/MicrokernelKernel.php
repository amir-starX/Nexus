<?php

declare(strict_types=1);

namespace Nexus\Kernels\Microkernel;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class MicrokernelKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'microkernel';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        if (!$context->container->has(PluginRegistry::class)) {
            return $this->invokeHandler($context, $ctx);
        }

        /** @var PluginRegistry $registry */
        $registry = $context->container->get(PluginRegistry::class);
        foreach ($registry->all() as $plugin) {
            if ($plugin instanceof PluginInterface) {
                $plugin->boot($context);
            }
        }

        return $this->invokeHandler($context, $ctx);
    }
}
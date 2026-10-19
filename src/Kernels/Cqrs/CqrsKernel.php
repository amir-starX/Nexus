<?php

declare(strict_types=1);

namespace Nexus\Kernels\Cqrs;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Kernels\Shared\CommandBus;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class CqrsKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'cqrs';
    }

    public function supports(RequestContext $ctx): bool
    {
        return ($ctx->request->attributes['command'] ?? null) !== null
            || parent::supports($ctx);
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        $command = $ctx->request->attributes['command'] ?? null;
        if ($command !== null) {
            $bus = $context->container->get(CommandBus::class);
            $result = $bus->dispatch($command);
            return $result instanceof Response ? $result : Response::json($result);
        }
        return $this->invokeHandler($context, $ctx);
    }
}
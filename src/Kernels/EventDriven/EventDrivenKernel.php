<?php

declare(strict_types=1);

namespace Nexus\Kernels\EventDriven;

use Nexus\Kernels\Shared\AbstractPatternKernel;
use Nexus\Kernels\Shared\EventDispatcher;
use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

final class EventDrivenKernel extends AbstractPatternKernel
{
    public function name(): string
    {
        return 'event-driven';
    }

    public function handle(KernelContext $context, RequestContext $ctx): Response
    {
        $response = $this->invokeHandler($context, $ctx);

        if ($context->events instanceof EventDispatcher) {
            $events = $ctx->request->attributes['events'] ?? [];
            if (is_array($events)) {
                foreach ($events as $event) {
                    if (is_object($event)) {
                        $context->events->dispatch($event);
                    }
                }
            }
        }

        return $response;
    }
}
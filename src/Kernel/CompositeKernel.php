<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Http\Request;
use Nexus\Http\Response;

final class CompositeKernel
{
    public function __construct(
        private readonly PatternResolver $resolver,
        private readonly KernelRegistry $registry,
    ) {
    }

    public function handle(KernelContext $context, Request $request): Response
    {
        $chain = $this->resolver->chain($request, $context);

        $response = null;
        foreach ($chain as $name) {
            $kernel = $this->registry->get($name);
            $response = $kernel->handle($context, new RequestContext($request, $response));
        }

        return $response ?? Response::notFound();
    }
}
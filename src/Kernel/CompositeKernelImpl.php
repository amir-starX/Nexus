<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Contracts\CompositeKernelInterface;
use Nexus\Contracts\PatternResolverInterface;
use Nexus\Http\Request;
use Nexus\Http\Response;

final class CompositeKernelImpl implements CompositeKernelInterface
{
    public function __construct(
        private readonly PatternResolverInterface $resolver,
        private readonly KernelRegistry $registry,
    ) {
    }

    public function handle(KernelContext $context, Request $request): Response
    {
        $chain = $this->resolver->chain($request, $context);
        $response = null;

        foreach ($chain as $name) {
            $kernel = $this->registry->get($name);
            $ctx = new RequestContext($request, $response);
            if (!$kernel->supports($ctx)) {
                continue;
            }
            $response = $kernel->handle($context, $ctx);
            if ($response !== null) {
                break;
            }
        }

        return $response ?? Response::notFound();
    }
}
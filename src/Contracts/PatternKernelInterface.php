<?php

declare(strict_types=1);

namespace Nexus\Contracts;

use Nexus\Http\Response;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\RequestContext;

interface PatternKernelInterface
{
    public function name(): string;

    public function supports(RequestContext $ctx): bool;

    public function handle(KernelContext $context, RequestContext $ctx): Response;
}
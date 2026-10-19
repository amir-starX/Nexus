<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Http\Request;
use Nexus\Http\Response;

final class RequestContext
{
    public function __construct(
        public readonly Request $request,
        public readonly ?Response $previous = null,
    ) {
    }

    public function previousResponse(): ?Response
    {
        return $this->previous;
    }
}
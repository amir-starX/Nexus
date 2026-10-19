<?php

declare(strict_types=1);

namespace Nexus\Kernels\EventDriven;

abstract class AbstractDomainEvent
{
    public readonly float $occurredAt;

    public function __construct()
    {
        $this->occurredAt = microtime(true);
    }
}
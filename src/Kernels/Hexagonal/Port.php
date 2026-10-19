<?php

declare(strict_types=1);

namespace Nexus\Kernels\Hexagonal;

interface Port
{
    public function name(): string;
}
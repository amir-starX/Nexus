<?php

declare(strict_types=1);

namespace Nexus\Routing;

final class Route
{
    /** @var array<string, string> */
    public array $params = [];

    public function __construct(
        public readonly string $path,
        public readonly array $methods,
        public readonly string $pattern,
        public readonly mixed $handler,
        public readonly ?string $architecture = null,
        public readonly ?string $module = null,
    ) {
    }
}
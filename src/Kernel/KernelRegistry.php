<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Contracts\PatternKernelInterface;
use Nexus\Exception\KernelException;

final class KernelRegistry
{
    /** @var array<string, PatternKernelInterface> */
    private array $kernels = [];

    public function register(PatternKernelInterface $kernel): void
    {
        $this->kernels[$kernel->name()] = $kernel;
    }

    public function get(string $name): PatternKernelInterface
    {
        return $this->kernels[$name]
            ?? throw new KernelException("Kernel not registered: {$name}");
    }

    public function has(string $name): bool
    {
        return isset($this->kernels[$name]);
    }

    /** @return string[] */
    public function names(): array
    {
        return array_keys($this->kernels);
    }
}
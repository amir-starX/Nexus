<?php

declare(strict_types=1);

namespace Nexus\Kernel;

final class ErrorPanelState
{
    /** @var array<int, array<string, mixed>> */
    private array $entries = [];

    public function record(\Throwable $e, array $context = []): void
    {
        $this->entries[] = [
            'exception' => $e,
            'context' => $context,
            'timestamp' => microtime(true),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->entries;
    }

    public function empty(): bool
    {
        return $this->entries === [];
    }
}
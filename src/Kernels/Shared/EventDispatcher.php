<?php

declare(strict_types=1);

namespace Nexus\Kernels\Shared;

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, array<int, array{0:int,1:callable}>> */
    private array $listeners = [];

    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][] = [$priority, $listener];
        usort(
            $this->listeners[$event],
            static fn (array $a, array $b): int => $b[0] <=> $a[0],
        );
    }

    public function dispatch(object $event): object
    {
        foreach ($this->listeners[$event::class] ?? [] as [, $listener]) {
            $listener($event);
        }
        return $event;
    }
}
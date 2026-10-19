<?php

declare(strict_types=1);

namespace Nexus\Kernels\Shared;

use Nexus\Exception\KernelException;

final class CommandBus
{
    /** @var array<string, callable(object): mixed> */
    private array $handlers = [];

    /** @var array<string, callable(object): mixed> */
    private array $queries = [];

    /** @var array<int, callable(object): void> */
    private array $middleware = [];

    public function registerCommand(string $command, callable $handler): void
    {
        $this->handlers[$command] = $handler;
    }

    public function registerQuery(string $query, callable $handler): void
    {
        $this->queries[$query] = $handler;
    }

    public function use(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(object $command): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            fn (callable $next, callable $mw) => fn (object $msg) => $mw($msg, $next),
            fn (object $msg) => $this->execute($this->handlers, $msg, 'command'),
        );

        return $pipeline($command);
    }

    public function ask(object $query): mixed
    {
        return $this->execute($this->queries, $query, 'query');
    }

    private function execute(array $map, object $message, string $kind): mixed
    {
        $class = $message::class;
        if (!isset($map[$class])) {
            throw new KernelException("No {$kind} handler for {$class}");
        }
        return $map[$class]($message);
    }
}
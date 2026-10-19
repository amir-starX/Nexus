<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Config\Config;
use Nexus\Container\Container;
use Nexus\Contracts\CompositeKernelInterface;
use Nexus\Contracts\HandlerResolverInterface;
use Nexus\Routing\Router;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

final class KernelContext
{
    public function __construct(
        public readonly string $basePath,
        public readonly Config $config,
        public readonly Container $container,
        public readonly Router $router,
        public readonly CompositeKernelInterface $dispatcher,
        public readonly HandlerResolverInterface $handlerResolver,
        public readonly ?EventDispatcherInterface $events = null,
        public readonly ?LoggerInterface $logger = null,
        public readonly ?ErrorPanelState $errorPanel = null,
    ) {
    }
}
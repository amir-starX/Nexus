<?php

declare(strict_types=1);

namespace Nexus\Adapters\PhpDi;

use DI\ContainerBuilder;
use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;
use Psr\Container\ContainerInterface;

final class PhpDiAdapter extends Adapter
{
    public function name(): string
    {
        return 'php-di';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(ContainerBuilder::class)) {
            return;
        }

        $builder = new ContainerBuilder();
        if (!$context->config->isDebug()) {
            $builder->enableCompilation($context->basePath . '/storage/cache/phpdi');
        }

        $container = $builder->build();
        $context->container->instance('phpdi.container', $container);
        $context->container->instance(ContainerInterface::class, $container);
    }
}
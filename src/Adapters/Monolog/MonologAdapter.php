<?php

declare(strict_types=1);

namespace Nexus\Adapters\Monolog;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;
use Psr\Log\LoggerInterface;

final class MonologAdapter extends Adapter
{
    public function name(): string
    {
        return 'monolog';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(Logger::class)) {
            return;
        }

        $path = (string) $context->config->get('adapters_options.monolog.path', $context->basePath . '/storage/logs/app.log');
        $level = (int) $context->config->get('adapters_options.monolog.level', Logger::DEBUG);

        $logger = new Logger('nexus');
        $logger->pushHandler(new StreamHandler($path, $level));

        $context->container->instance('monolog', $logger);
        $context->container->instance(LoggerInterface::class, $logger);
    }
}
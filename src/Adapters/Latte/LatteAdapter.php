<?php

declare(strict_types=1);

namespace Nexus\Adapters\Latte;

use Latte\Engine;
use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class LatteAdapter extends Adapter
{
    public function name(): string
    {
        return 'latte';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(Engine::class)) {
            return;
        }

        $options = $context->config->get('adapters_options.latte', []);
        $tempDir = is_array($options) ? (string) ($options['temp_dir'] ?? $context->basePath . '/storage/cache/latte') : $context->basePath . '/storage/cache/latte';

        $latte = new Engine();
        $latte->setTempDirectory($tempDir);

        $context->container->instance('latte', $latte);
        $context->container->instance(Engine::class, $latte);
    }
}
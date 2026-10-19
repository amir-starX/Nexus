<?php

declare(strict_types=1);

namespace Nexus\Adapters\Blade;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class BladeAdapter extends Adapter
{
    public function name(): string
    {
        return 'blade';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(\Illuminate\View\Factory::class)) {
            return;
        }

        $options = $context->config->get('adapters_options.blade', []);
        $views = is_array($options) ? (string) ($options['views'] ?? $context->basePath . '/resources/views') : $context->basePath . '/resources/views';
        $cache = is_array($options) ? (string) ($options['cache'] ?? $context->basePath . '/storage/cache/blade') : $context->basePath . '/storage/cache/blade';

        $filesystem = new \Illuminate\Filesystem\Filesystem();
        $compiler = new \Illuminate\View\Compilers\BladeCompiler($filesystem, $cache);
        $engineResolver = new \Illuminate\View\Engines\EngineResolver();
        $engineResolver->register('blade', fn () => new \Illuminate\View\Engines\CompilerEngine($compiler));
        $finder = new \Illuminate\View\FileViewFinder($filesystem, [$views]);
        $factory = new \Illuminate\View\Factory($engineResolver, $finder, new \Illuminate\Events\Dispatcher());

        $context->container->instance('blade.compiler', $compiler);
        $context->container->instance('blade.factory', $factory);
    }
}
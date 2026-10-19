<?php

declare(strict_types=1);

namespace Nexus\Adapters\Twig;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigAdapter extends Adapter
{
    public function name(): string
    {
        return 'twig';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(Environment::class)) {
            return;
        }

        $paths = $context->config->get('adapters_options.twig.paths', []);
        $cache = $context->config->get('adapters_options.twig.cache', null);

        $loader = new FilesystemLoader(is_array($paths) ? $paths : []);
        $twig = new Environment($loader, [
            'cache' => is_string($cache) ? $cache : false,
            'debug' => $context->config->isDebug(),
        ]);

        $context->container->instance('twig', $twig);
        $context->container->instance(Environment::class, $twig);
    }
}
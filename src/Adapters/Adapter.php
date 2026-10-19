<?php

declare(strict_types=1);

namespace Nexus\Adapters;

use Nexus\Contracts\AdapterInterface;
use Nexus\Kernel\KernelContext;

abstract class Adapter implements AdapterInterface
{
    public function boot(KernelContext $context): void
    {
    }

    protected function requirePackage(KernelContext $context, string $package): bool
    {
        if (!class_exists($package)) {
            $auto = (bool) $context->config->get('plugins.auto_install', true);
            if ($auto) {
                \Nexus\Support\ComposerInstaller::install($context->basePath, $package);
            }
            return class_exists($package);
        }
        return true;
    }
}
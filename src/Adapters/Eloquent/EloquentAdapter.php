<?php

declare(strict_types=1);

namespace Nexus\Adapters\Eloquent;

use Illuminate\Database\Capsule\Manager as Capsule;
use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class EloquentAdapter extends Adapter
{
    public function name(): string
    {
        return 'eloquent';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(Capsule::class)) {
            return;
        }

        $options = $context->config->get('adapters_options.eloquent', []);
        if (!is_array($options) || $options === []) {
            return;
        }

        $capsule = new Capsule();
        $capsule->addConnection($options);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $context->container->instance('eloquent.capsule', $capsule);
    }
}
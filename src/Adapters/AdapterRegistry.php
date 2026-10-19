<?php

declare(strict_types=1);

namespace Nexus\Adapters;

use Nexus\Contracts\AdapterInterface;
use Nexus\Exception\KernelException;
use Nexus\Kernel\KernelContext;

final class AdapterRegistry
{
    /** @var array<string, string> */
    private array $classes = [
        'illuminate' => Illuminate\IlluminateAdapter::class,
        'symfony' => Symfony\SymfonyAdapter::class,
        'slim' => Slim\SlimAdapter::class,
        'laminas' => Laminas\LaminasAdapter::class,
        'doctrine' => Doctrine\DoctrineAdapter::class,
        'eloquent' => Eloquent\EloquentAdapter::class,
        'twig' => Twig\TwigAdapter::class,
        'blade' => Blade\BladeAdapter::class,
        'latte' => Latte\LatteAdapter::class,
        'php-di' => PhpDi\PhpDiAdapter::class,
        'monolog' => Monolog\MonologAdapter::class,
        'fastroute' => FastRoute\FastRouteAdapter::class,
        'league' => League\LeagueAdapter::class,
    ];

    public function boot(KernelContext $context): void
    {
        $enabled = $context->config->get('adapters', []);
        if (!is_array($enabled)) {
            return;
        }

        foreach ($enabled as $name) {
            if (!is_string($name)) {
                continue;
            }
            $class = $this->classes[$name] ?? null;
            if ($class === null || !class_exists($class)) {
                continue;
            }
            $adapter = $context->container->get($class);
            if (!$adapter instanceof AdapterInterface) {
                throw new KernelException("Adapter must implement AdapterInterface: {$class}");
            }
            $adapter->boot($context);
        }
    }
}
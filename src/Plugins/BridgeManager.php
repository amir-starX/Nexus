<?php

declare(strict_types=1);

namespace Nexus\Plugins;

use Nexus\Contracts\PluginBridgeInterface;
use Nexus\Exception\KernelException;
use Nexus\Kernel\KernelContext;
use Nexus\Support\ComposerInstaller;

final class BridgeManager
{
    /** @var array<string, PluginBridgeInterface> */
    private array $bridges = [];

    public function __construct(private readonly KernelContext $context)
    {
    }

    public function register(PluginBridgeInterface $bridge): void
    {
        $this->bridges[$bridge->name()] = $bridge;
    }

    public function get(string $name): PluginBridgeInterface
    {
        return $this->bridges[$name]
            ?? throw new KernelException("Plugin bridge not registered: {$name}");
    }

    /** @return array<string, PluginBridgeInterface> */
    public function all(): array
    {
        return $this->bridges;
    }

    public function boot(): void
    {
        $names = $this->context->config->get('plugins.bridges', []);
        if (!is_array($names)) {
            return;
        }

        foreach ($names as $definition) {
            $this->resolve($definition);
        }
    }

    private function resolve(mixed $definition): void
    {
        if (!is_array($definition)) {
            return;
        }

        $class = $definition['class'] ?? null;
        $requires = $definition['requires'] ?? [];

        if (!is_string($class)) {
            return;
        }

        if (is_array($requires)) {
            foreach ($requires as $package) {
                if (!is_string($package)) {
                    continue;
                }
                if (!$this->packageInstalled($package)) {
                    ComposerInstaller::install($this->context->basePath, $package);
                }
            }
        }

        if (!class_exists($class)) {
            throw new KernelException("Plugin bridge class not found: {$class}");
        }

        $instance = $this->context->container->get($class);
        if (!$instance instanceof PluginBridgeInterface) {
            throw new KernelException("Plugin bridge must implement PluginBridgeInterface: {$class}");
        }

        foreach ($instance->requires() as $package) {
            if (!$this->packageInstalled($package)) {
                ComposerInstaller::install($this->context->basePath, $package);
            }
        }

        $instance->register($this->context);
        $this->register($instance);
    }

    private function packageInstalled(string $package): bool
    {
        $map = [
            'illuminate/container' => \Illuminate\Container\Container::class,
            'illuminate/database' => \Illuminate\Database\Capsule\Manager::class,
            'illuminate/view' => \Illuminate\View\Factory::class,
            'symfony/console' => \Symfony\Component\Console\Application::class,
            'symfony/event-dispatcher' => \Symfony\Component\EventDispatcher\EventDispatcher::class,
            'symfony/http-foundation' => \Symfony\Component\HttpFoundation\Request::class,
            'slim/slim' => \Slim\Factory\AppFactory::class,
            'laminas/laminas-servicemanager' => \Laminas\ServiceManager\ServiceManager::class,
            'doctrine/orm' => \Doctrine\ORM\EntityManager::class,
            'twig/twig' => \Twig\Environment::class,
            'latte/latte' => \Latte\Engine::class,
            'monolog/monolog' => \Monolog\Logger::class,
            'nikic/fast-route' => \FastRoute\RouteCollector::class,
            'league/container' => \League\Container\Container::class,
            'php-di/php-di' => \DI\ContainerBuilder::class,
        ];

        $probe = $map[$package] ?? null;
        return $probe !== null && class_exists($probe);
    }
}
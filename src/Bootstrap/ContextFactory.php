<?php

declare(strict_types=1);

namespace Nexus\Bootstrap;

use Nexus\Adapters\AdapterRegistry;
use Nexus\Config\Config;
use Nexus\Container\ContainerFactory;
use Nexus\Contracts\CompositeKernelInterface;
use Nexus\Contracts\HandlerResolverInterface;
use Nexus\Contracts\PatternResolverInterface;
use Nexus\Kernel\ArchitectureConfig;
use Nexus\Kernel\CompositeKernelImpl;
use Nexus\Kernel\DefaultPatternResolver;
use Nexus\Kernel\ErrorPanelState;
use Nexus\Kernel\HandlerResolver;
use Nexus\Kernel\KernelContext;
use Nexus\Kernel\KernelLoader;
use Nexus\Kernel\KernelRegistry;
use Nexus\Plugins\BridgeBootstrapper;
use Nexus\Routing\RouterFactory;

final class ContextFactory
{
    public static function build(string $basePath, array $configData): KernelContext
    {
        $config = new Config($configData, $basePath);
        $container = ContainerFactory::build($config, $basePath);
        $router = RouterFactory::build($config, $container);

        $archConfig = ArchitectureConfig::fromConfig($config);
        $resolver = new DefaultPatternResolver($archConfig);
        $registry = new KernelRegistry();
        $composite = new CompositeKernelImpl($resolver, $registry);
        $handlerResolver = new HandlerResolver($container);
        $errorPanel = new ErrorPanelState();

        $container->instance(Config::class, $config);
        $container->instance(ArchitectureConfig::class, $archConfig);
        $container->instance(PatternResolverInterface::class, $resolver);
        $container->instance(KernelRegistry::class, $registry);
        $container->instance(CompositeKernelInterface::class, $composite);
        $container->instance(HandlerResolverInterface::class, $handlerResolver);
        $container->instance(ErrorPanelState::class, $errorPanel);

        $context = new KernelContext(
            basePath: $basePath,
            config: $config,
            container: $container,
            router: $router,
            dispatcher: $composite,
            handlerResolver: $handlerResolver,
            events: null,
            logger: null,
            errorPanel: $errorPanel,
        );

        KernelLoader::register($context, $registry);
        (new AdapterRegistry())->boot($context);
        BridgeBootstrapper::boot($context);

        $providers = ProviderLoader::load($context);
        ProviderLoader::registerAll($context, $providers);
        ProviderLoader::bootAll($context, $providers);

        return $context;
    }
}
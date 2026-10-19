<?php

declare(strict_types=1);

namespace Nexus\Kernel;

use Nexus\Contracts\PatternKernelInterface;
use Nexus\Exception\KernelException;

final class KernelLoader
{
    private const BUILTIN = [
        'mvc' => \Nexus\Kernels\Mvc\MvcKernel::class,
        'hmvc' => \Nexus\Kernels\Hmvc\HmvcKernel::class,
        'adr' => \Nexus\Kernels\Adr\AdrKernel::class,
        'cqrs' => \Nexus\Kernels\Cqrs\CqrsKernel::class,
        'hexagonal' => \Nexus\Kernels\Hexagonal\HexagonalKernel::class,
        'microkernel' => \Nexus\Kernels\Microkernel\MicrokernelKernel::class,
        'layered' => \Nexus\Kernels\Layered\LayeredKernel::class,
        'event-driven' => \Nexus\Kernels\EventDriven\EventDrivenKernel::class,
        'modular' => \Nexus\Kernels\Modular\ModularKernel::class,
    ];

    public static function register(KernelContext $context, KernelRegistry $registry): void
    {
        $custom = $context->config->get('architecture.kernels', []);
        if (is_array($custom)) {
            foreach ($custom as $name => $class) {
                if (!is_string($class) || !class_exists($class)) {
                    continue;
                }
                $instance = $context->container->get($class);
                if (!$instance instanceof PatternKernelInterface) {
                    throw new KernelException("Custom kernel must implement PatternKernelInterface: {$class}");
                }
                $registry->register($instance);
            }
        }

        foreach (self::BUILTIN as $name => $class) {
            if (!class_exists($class)) {
                continue;
            }
            if ($registry->has($name)) {
                continue;
            }
            /** @var PatternKernelInterface $instance */
            $instance = $context->container->get($class);
            $registry->register($instance);
        }
    }
}
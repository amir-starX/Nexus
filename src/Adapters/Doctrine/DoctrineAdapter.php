<?php

declare(strict_types=1);

namespace Nexus\Adapters\Doctrine;

use Nexus\Adapters\Adapter;
use Nexus\Kernel\KernelContext;

final class DoctrineAdapter extends Adapter
{
    public function name(): string
    {
        return 'doctrine';
    }

    public function boot(KernelContext $context): void
    {
        if (!class_exists(\Doctrine\ORM\EntityManager::class)) {
            return;
        }

        $params = $context->config->get('adapters_options.doctrine', []);
        if (!is_array($params) || $params === []) {
            return;
        }

        $setup = \Doctrine\ORM\Tools\Setup::createAttributeMetadataConfiguration(
            (array) ($params['paths'] ?? []),
            (bool) ($params['dev_mode'] ?? $context->config->isDebug()),
        );

        $em = \Doctrine\ORM\EntityManager::create($params['connection'] ?? [], $setup);
        $context->container->instance('doctrine.entity_manager', $em);
        $context->container->instance(\Doctrine\ORM\EntityManagerInterface::class, $em);
    }
}
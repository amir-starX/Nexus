<?php

declare(strict_types=1);

namespace Nexus\ErrorPanel;

use Nexus\Kernel\KernelContext;

final class PanelFactory
{
    public static function fromContext(KernelContext $context): Panel
    {
        $state = $context->errorPanel ?? new \Nexus\Kernel\ErrorPanelState();
        return new Panel($context->config, $state, $context);
    }
}
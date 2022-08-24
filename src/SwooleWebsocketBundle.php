<?php

namespace Cydrickn\SwooleWebsocketBundle;

use Cydrickn\SwooleWebsocketBundle\DependencyInjection\SwooleWebsocketExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SwooleWebsocketBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SwooleWebsocketExtension();
    }
}

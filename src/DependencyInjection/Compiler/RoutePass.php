<?php

namespace Cydrickn\SwooleWebsocketBundle\DependencyInjection\Compiler;

use Cydrickn\SocketIO\Router\RouterProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class RoutePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        try {
            $providerDefinition = $container->getDefinition(RouterProvider::class);
            foreach ($container->findTaggedServiceIds('websocket.route') as $class => $tags) {
                $providerDefinition->addMethodCall('addRoute', [new Reference($class)]);
            }
        } catch (ServiceNotFoundException $exception) {
            // Nothing to do
        }
    }
}

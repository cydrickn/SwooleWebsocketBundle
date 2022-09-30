<?php

namespace Cydrickn\SwooleWebsocketBundle;

use Cydrickn\SocketIO\Router\Route;
use Cydrickn\SwooleWebsocketBundle\Attribute\RouteAttribute;
use Cydrickn\SwooleWebsocketBundle\DependencyInjection\Compiler\RoutePass;
use Cydrickn\SwooleWebsocketBundle\DependencyInjection\SwooleWebsocketExtension;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SwooleWebsocketBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
    {
        $container->registerAttributeForAutoconfiguration(
            RouteAttribute::class,
            static function (
                ChildDefinition $definition,
                RouteAttribute $attribute,
                \ReflectionMethod $method
            ) use ($container): void {
                $class = $method->getDeclaringClass()->getName();
                $path = $attribute->path;
                $method = $method->getName();

                $routeDefinition = new Definition(Route::class, [[new Reference($class), $method], $path]);
                $routeDefinition->addTag('websocket.route');
                $id = 'routes.' . $path;
                $container->setDefinition($id, $routeDefinition);
            }
        );

        parent::build($container);
        $container->addCompilerPass(new RoutePass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SwooleWebsocketExtension();
    }
}

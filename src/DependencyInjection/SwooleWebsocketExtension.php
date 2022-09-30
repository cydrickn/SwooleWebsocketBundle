<?php

namespace Cydrickn\SwooleWebsocketBundle\DependencyInjection;

use Cydrickn\SwooleWebsocketBundle\Factory\ServerFactory;
use Cydrickn\SwooleWebsocketBundle\Runtime\Runtime;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SwooleWebsocketExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        global $runtime;

        $configDir = new FileLocator(__DIR__ . '/../../config');
        $loader = new YamlFileLoader($container, $configDir);
        $loader->load('services.yaml');

        if ($runtime instanceof Runtime) {
            $server = $GLOBALS['runtimeServer'];
            $definition = new Definition(get_class($server));
            $definition->setPublic(true);
            $definition->setFactory([ServerFactory::class, 'handle']);
            $container->setDefinition(get_class($server), $definition);
        }
    }
}

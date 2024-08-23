<?php

namespace App\DependencyInjection\ValidationPasses;

use App\Pos\Providers\PosInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @class PosProviderValidationPass
 * @package App\DependencyInjection\ValidationPasses
 */
class PosProviderValidationPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('pos.provider');
        $validProvidersServices = [];

        foreach ($taggedServices as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();

            if (in_array(PosInterface::class, class_implements($class))) {
                $validProvidersServices[] = new Reference($serviceId);
            }
        }

        $posFactoryDefinition = $container->getDefinition('App\Pos\PosFactory');
        $posFactoryDefinition->replaceArgument('$providers', $validProvidersServices);
    }
}

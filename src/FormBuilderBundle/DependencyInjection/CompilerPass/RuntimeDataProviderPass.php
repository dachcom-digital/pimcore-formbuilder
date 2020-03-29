<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\RuntimeDataProviderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RuntimeDataProviderPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(RuntimeDataProviderRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.runtime_data_provider') as $id => $tags) {
            $definition->addMethodCall('register', [new Reference($id)]);
        }
    }
}

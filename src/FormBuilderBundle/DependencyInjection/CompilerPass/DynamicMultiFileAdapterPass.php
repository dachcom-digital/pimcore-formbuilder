<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\DynamicMultiFileAdapterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DynamicMultiFileAdapterPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(DynamicMultiFileAdapterRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.dynamic_multi_file.adapter') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [$id, new Reference($id)]);
            }
        }
    }
}

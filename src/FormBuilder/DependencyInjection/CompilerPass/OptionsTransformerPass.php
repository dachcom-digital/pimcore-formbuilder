<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class OptionsTransformerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('form_builder.registry.options_transformer');
        foreach ($container->findTaggedServiceIds('form_builder.transformer.options') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [$id, new Reference($id)]);
            }
        }
    }
}
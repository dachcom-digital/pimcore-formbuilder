<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class OptionsTransformerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(OptionsTransformerRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.transformer.options') as $id => $tags) {
            $definition->addMethodCall('register', [$id, new Reference($id)]);
        }

        foreach ($container->findTaggedServiceIds('form_builder.transformer.dynamic_options') as $id => $tags) {
            $definition->addMethodCall('registerDynamic', [$id, new Reference($id)]);
        }
    }
}

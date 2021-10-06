<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\ChoiceBuilderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ChoiceBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(ChoiceBuilderRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.dynamic_choice_builder') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [$id, $attributes['label'], new Reference($id)]);
            }
        }
    }
}

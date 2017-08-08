<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class FormTypePass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $formTypeRegistryDefinition = $container->getDefinition('form_builder.registry.form_type');
        foreach ($container->findTaggedServiceIds('form_builder.form_type') as $id => $tags) {
            foreach ($tags as $attributes) {
                $formTypeRegistryDefinition->addMethodCall('register', [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
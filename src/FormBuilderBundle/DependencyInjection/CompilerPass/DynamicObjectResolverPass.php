<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DynamicObjectResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(DynamicObjectResolverRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.output_workflow.object.dynamic_resolver') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['identifier'])) {
                    throw new InvalidConfigurationException(sprintf('You need to define a valid identifier for dynamic object resolver "%s"', $id));
                } elseif (!isset($attributes['label'])) {
                    throw new InvalidConfigurationException(sprintf('You need to define a valid label for dynamic object resolver "%s"', $id));
                }

                $definition->addMethodCall('register', [$attributes['identifier'], $attributes['label'], new Reference($id)]);
            }
        }
    }
}

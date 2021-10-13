<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\OutputTransformerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class OutputTransformerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(OutputTransformerRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.transformer.output') as $id => $tags) {
            foreach ($tags as $attributes) {
                $channel = !isset($attributes['channel']) || empty($attributes['channel']) ? '_all' : $attributes['channel'];
                $definition->addMethodCall('register', [$attributes['type'], $channel, new Reference($id)]);
            }
        }
    }
}

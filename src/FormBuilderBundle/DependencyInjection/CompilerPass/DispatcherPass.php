<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\DispatcherRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class DispatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(DispatcherRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.dispatcher') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [$attributes['type'], new Reference($id)]);
            }
        }
    }
}

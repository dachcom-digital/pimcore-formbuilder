<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\ApiProviderRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ApiProviderPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(ApiProviderRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.api.provider') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['identifier'])) {
                    throw new InvalidConfigurationException(sprintf('You need to define a valid identifier for api provider "%s"', $id));
                }

                $definition->addMethodCall('register', [$attributes['identifier'], new Reference($id)]);
            }
        }
    }
}

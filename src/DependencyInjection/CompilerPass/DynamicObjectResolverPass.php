<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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

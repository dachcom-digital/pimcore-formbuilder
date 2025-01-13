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

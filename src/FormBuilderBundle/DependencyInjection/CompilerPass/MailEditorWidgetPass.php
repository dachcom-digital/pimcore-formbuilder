<?php

namespace FormBuilderBundle\DependencyInjection\CompilerPass;

use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MailEditorWidgetPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(MailEditorWidgetRegistry::class);
        foreach ($container->findTaggedServiceIds('form_builder.mail_editor.widget') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('register', [$attributes['type'], new Reference($id)]);
            }
        }
    }
}

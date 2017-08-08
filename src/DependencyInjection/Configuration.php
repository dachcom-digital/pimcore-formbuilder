<?php

namespace FormBuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('form_builder');

        $rootNode
            ->children()

                ->arrayNode('area')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('presets')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('form')
                    ->addDefaultsIfNotSet()
                    ->children()

                        ->arrayNode('templates')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('key')->isRequired()->end()
                                    ->scalarNode('value')->isRequired()->end()
                                    ->booleanNode('default')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('field')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('templates')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('key')->isRequired()->end()
                                            ->scalarNode('value')->isRequired()->end()
                                            ->booleanNode('default')->isRequired()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()

                ->arrayNode('admin')
                    ->children()
                        ->arrayNode('active_elements')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('fields')
                                ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('backend_base_field_type_groups')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('text')->end()
                            ->scalarNode('icon_class')->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('backend_base_field_type_config')
                    ->children()
                        ->arrayNode('tabs')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('id')->end()
                                    ->scalarNode('label')->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('display_groups')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('tab_id')->end()
                                    ->scalarNode('id')->end()
                                    ->scalarNode('label')->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('fields')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('display_group_id')->end()
                                    ->scalarNode('id')
                                        ->isRequired()
                                        ->validate()
                                            ->ifInArray(['display_name', 'type', 'width', 'order', 'options'])
                                            ->thenInvalid('%s is a reserved field type id.')
                                        ->end()
                                    ->end()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('label')->end()
                                    ->variableNode('config')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('backend_field_type_config')

                    ->useAttributeAsKey('name')
                    ->prototype('array')

                        ->children()
                            ->scalarNode('form_type_group')->end()
                            ->scalarNode('icon_class')->end()
                            ->arrayNode('tabs')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('id')->end()
                                        ->scalarNode('label')->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('display_groups')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('tab_id')->end()
                                    ->scalarNode('id')->end()
                                    ->scalarNode('label')->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('fields')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('display_group_id')->end()
                                    ->scalarNode('id')
                                        ->isRequired()
                                        ->validate()
                                            ->ifInArray(['display_name', 'type', 'width', 'order', 'options'])
                                            ->thenInvalid('%s is a reserved field type id.')
                                        ->end()
                                    ->end()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('label')->end()
                                    ->variableNode('config')->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()

                ->end()

            ->end();

        return $treeBuilder;
    }
}
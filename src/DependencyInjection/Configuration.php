<?php

namespace FormBuilderBundle\DependencyInjection;

use FormBuilderBundle\DynamicMultiFile\Adapter\DropZoneAdapter;
use FormBuilderBundle\EventSubscriber\SignalStorage\FormDataSignalStorage;
use FormBuilderBundle\Manager\DoubleOptInManager;
use FormBuilderBundle\Storage\SessionStorageProvider;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('form_builder');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('dynamic_multi_file_adapter')->defaultValue(DropZoneAdapter::class)->end()
                ->variableNode('form_attributes')->end()
            ->end();

        $rootNode->append($this->buildFunnelNode());
        $rootNode->append($this->buildDoubleOptInNode());
        $rootNode->append($this->createPersistenceNode());
        $rootNode->append($this->buildFlagsNode());
        $rootNode->append($this->buildSpamProductionNode());
        $rootNode->append($this->buildAreaNode());
        $rootNode->append($this->buildFormConfigurationNode());
        $rootNode->append($this->buildAdminConfigurationNode());
        $rootNode->append($this->buildBackendBaseFieldTypeGroupsNode());
        $rootNode->append($this->buildBackendBaseFieldTypeConfigNode());
        $rootNode->append($this->buildValidationConstraintsNode());
        $rootNode->append($this->buildContainerTypesNode());
        $rootNode->append($this->buildConditionalLogicNode());
        $rootNode->append($this->buildFormTypesNode());
        $rootNode->append($this->buildEmailConfigurationNode());

        return $treeBuilder;
    }

    private function buildConditionalLogicNode(): NodeDefinition
    {
        $builder = new TreeBuilder('conditional_logic');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('action')
                ->useAttributeAsKey('id')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->defaultValue(null)->end()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('icon')->isRequired()->end()
                            ->arrayNode('form')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->validate()
                                        ->ifTrue(function ($v) {
                                            return !empty($v['conditional']) && empty($v['conditional_identifier']);
                                        })
                                        ->thenInvalid('conditional form fields requires a valid conditional_identifier.')
                                    ->end()
                                    ->validate()
                                        ->ifTrue(function ($v) {
                                            return !empty($v['conditional']) && $v['type'] !== 'conditional_select';
                                        })
                                        ->thenInvalid('conditional form is only allowed for type "conditional_select".')
                                    ->end()
                                    ->children()
                                        ->scalarNode('type')->isRequired()->end()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->variableNode('config')->end()
                                        ->scalarNode('options_transformer')->defaultValue(null)->end()
                                        ->scalarNode('conditional_identifier')
                                            ->validate()
                                                ->ifTrue(function ($v) {
                                                    return empty($v);
                                                })
                                                ->thenUnset()
                                            ->end()
                                        ->end()
                                        ->arrayNode('conditional')
                                            ->useAttributeAsKey('name')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('type')->isRequired()->end()
                                                    ->scalarNode('label')->isRequired()->end()
                                                    ->variableNode('config')->end()
                                                    ->scalarNode('options_transformer')->defaultValue(null)->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('condition')
                ->useAttributeAsKey('id')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->defaultValue(null)->end()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('icon')->isRequired()->end()
                            ->arrayNode('form')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('type')->isRequired()->end()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->variableNode('config')->end()
                                        ->scalarNode('options_transformer')->defaultValue(null)->end()
                                        ->scalarNode('conditional_identifier')
                                            ->validate()
                                                ->ifTrue(function ($v) {
                                                    return empty($v);
                                                })
                                                ->thenUnset()
                                            ->end()
                                        ->end()
                                        ->arrayNode('conditional')
                                            ->useAttributeAsKey('name')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('type')->isRequired()->end()
                                                    ->scalarNode('label')->isRequired()->end()
                                                    ->variableNode('config')->end()
                                                    ->scalarNode('options_transformer')->defaultValue(null)->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildFormTypesNode(): NodeDefinition
    {
        $builder = new TreeBuilder('types');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('output_transformer')->cannotBeEmpty()->defaultValue('fallback_transformer')->end()
                    ->scalarNode('input_transformer')->defaultNull()->end()
                    ->scalarNode('class')->cannotBeEmpty()->end()
                        ->arrayNode('configurations')
                        ->scalarPrototype()->cannotBeEmpty()->end()
                    ->end()
                    ->arrayNode('backend')
                        ->children()
                            ->scalarNode('form_type_group')->isRequired()->end()
                            ->scalarNode('label')->isRequired()->end()
                            ->scalarNode('icon_class')->end()
                            ->arrayNode('output_workflow')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('object')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->arrayNode('allowed_class_types')
                                                ->scalarPrototype()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('constraints')
                                ->beforeNormalization()
                                    ->ifTrue(function ($value) {
                                        // legacy
                                        return is_bool($value);
                                    })
                                    ->then(function ($value) {
                                        return $value === true
                                            ? ['enabled' => ['all']]
                                            : ['disabled' => ['all']];
                                    })
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($value) {
                                        return count($value['enabled']) > 0 && count($value['disabled']) > 0;
                                    })
                                    ->thenInvalid('%s is invalid, only one node can be defined ("enabled" or "disabled").')
                                ->end()
                                ->validate()
                                    ->always(function ($value) {
                                        if (isset($value['enabled']) && in_array('all', $value['enabled'], true)) {
                                            return ['disabled' => []];
                                        }

                                        if (isset($value['disabled']) && in_array('all', $value['disabled'], true)) {
                                            return ['enabled' => []];
                                        }

                                        if (isset($value['enabled']) && !empty($value['enabled'])) {
                                            return ['enabled' => $value['enabled']];
                                        }

                                        if (isset($value['disabled']) && !empty($value['disabled'])) {
                                            return ['disabled' => $value['disabled']];
                                        }

                                        return $value;
                                    })
                                ->end()
                                ->children()
                                    ->arrayNode('enabled')
                                        ->scalarPrototype()->end()
                                    ->end()
                                    ->arrayNode('disabled')
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('tabs')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('label')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('display_groups')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('tab_id')->isRequired()->end()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->booleanNode('collapsed')->defaultFalse()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('fields')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('display_group_id')
                                            ->isRequired()
                                            ->validate()
                                                ->ifInArray(['display_name', 'type', 'template', 'order', 'options'])
                                                ->thenInvalid('%s is a reserved field type id.')
                                            ->end()
                                        ->end()
                                        ->scalarNode('type')->isRequired()->end()
                                        ->scalarNode('label')->isRequired()->end()
                                        ->scalarNode('options_transformer')->defaultValue(null)->end()
                                        ->variableNode('config')->end()
                                    ->end()
                                    ->canBeUnset()
                                    ->canBeDisabled()
                                    ->treatNullLike(['enabled' => false])
                                    ->beforeNormalization()
                                        ->ifNull()
                                        ->then(function ($v) {
                                            return ['display_group_id' => null, 'type' => null, 'label' => null, 'enabled' => false];
                                        })
                                    ->end()
                                    ->validate()
                                        ->ifTrue(function ($v) {
                                            return $v['enabled'] === false;
                                        })
                                        ->then(function ($v) {
                                            return false;
                                        })
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('dynamic_fields')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('source')->isRequired()->end()
                                        ->scalarNode('options_transformer')->isRequired()->end()
                                        ->variableNode('config')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildEmailConfigurationNode(): NodeDefinition
    {
        $builder = new TreeBuilder('email');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('html_2_text_options')
                    ->children()
                        ->scalarNode('header_style')->cannotBeEmpty()->defaultValue('setext')->end()
                        ->booleanNode('suppress_errors')->defaultValue(true)->end()
                        ->booleanNode('strip_tags')->defaultValue(false)->end()
                        ->scalarNode('remove_nodes')->cannotBeEmpty()->defaultValue('')->end()
                        ->booleanNode('hard_break')->defaultValue(false)->end()
                        ->scalarNode('list_item_style')->cannotBeEmpty()->defaultValue('-')->end()
                        ->booleanNode('preserve_comments')->defaultValue(false)->end()
                        ->booleanNode('use_autolinks')->defaultValue(false)->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildBackendBaseFieldTypeConfigNode(): NodeDefinition
    {
        $builder = new TreeBuilder('backend_base_field_type_config');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('tabs')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('label')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('display_groups')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('tab_id')->isRequired()->end()
                            ->scalarNode('label')->isRequired()->end()
                            ->booleanNode('collapsed')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('fields')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('display_group_id')
                                ->isRequired()
                                ->validate()
                                    ->ifInArray(['display_name', 'type', 'template', 'order', 'options'])
                                    ->thenInvalid('%s is a reserved field type id.')
                                ->end()
                            ->end()
                            ->scalarNode('type')->isRequired()->end()
                            ->scalarNode('label')->isRequired()->end()
                            ->scalarNode('options_transformer')->defaultValue(null)->end()
                            ->variableNode('config')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildContainerTypesNode(): NodeDefinition
    {
        $builder = new TreeBuilder('container_types');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('class')->end()
                    ->scalarNode('label')->end()
                    ->scalarNode('icon_class')->end()
                    ->arrayNode('output_workflow')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('object')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('allowed_class_types')
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('configuration')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('type')->isRequired()->end()
                                ->scalarNode('label')->isRequired()->end()
                                ->scalarNode('options_transformer')->defaultValue(null)->end()
                                ->variableNode('config')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->canBeUnset()
                ->canBeDisabled()
                ->treatNullLike(['enabled' => false])
                ->validate()
                    ->ifTrue(function ($v) {
                        return $v['enabled'] === false;
                    })
                    ->thenUnset()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildValidationConstraintsNode(): NodeDefinition
    {
        $builder = new TreeBuilder('validation_constraints');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('class')->end()
                    ->scalarNode('label')->end()
                    ->scalarNode('icon_class')->end()
                ->end()
                ->canBeUnset()
                ->canBeDisabled()
                ->treatNullLike(['enabled' => false])
                ->validate()
                    ->ifTrue(function ($v) {
                        return $v['enabled'] === false;
                    })
                    ->thenUnset()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildBackendBaseFieldTypeGroupsNode(): NodeDefinition
    {
        $builder = new TreeBuilder('backend_base_field_type_groups');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('label')->end()
                    ->scalarNode('icon_class')->end()
                ->end()
                ->canBeUnset()
                ->canBeDisabled()
                ->treatNullLike(['enabled' => false])
                ->validate()
                    ->ifTrue(function ($v) {
                        return $v['enabled'] === false;
                    })
                    ->thenUnset()
                ->end()
            ->end()
            ;

        return $rootNode;
    }

    private function buildAdminConfigurationNode(): NodeDefinition
    {
        $builder = new TreeBuilder('admin');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('active_elements')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('fields')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('inactive_elements')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('fields')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildFormConfigurationNode(): NodeDefinition
    {
        $builder = new TreeBuilder('form');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('templates')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('label')->isRequired()->end()
                            ->scalarNode('value')->isRequired()->end()
                            ->booleanNode('default')->isRequired()->end()
                        ->end()
                        ->canBeUnset()
                        ->canBeDisabled()
                        ->treatNullLike(['enabled' => false])
                        ->validate()
                            ->ifTrue(function ($v) {
                                return $v['enabled'] === false;
                            })
                            ->thenUnset()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('field')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('templates')
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('label')->isRequired()->end()
                                    ->scalarNode('value')->isRequired()->end()
                                    ->booleanNode('default')->isRequired()->end()
                                ->end()
                                ->canBeUnset()
                                ->canBeDisabled()
                                ->treatNullLike(['enabled' => false])
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return $v['enabled'] === false;
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildAreaNode(): NodeDefinition
    {
        $builder = new TreeBuilder('area');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('presets')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('nice_name')->isRequired()->end()
                            ->scalarNode('admin_description')->isRequired()->end()
                            ->arrayNode('sites')
                                ->useAttributeAsKey('name')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    private function buildSpamProductionNode(): NodeDefinition
    {
        $builder = new TreeBuilder('spam_protection');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('honeypot')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('field_name')->defaultValue('inputUserName')->end()
                        ->booleanNode('enable_inline_style')->defaultTrue()->end()
                        ->booleanNode('enable_role_attribute')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('recaptcha_v3')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('site_key')->defaultNull()->end()
                        ->scalarNode('secret_key')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('friendly_captcha')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('site_key')->defaultNull()->end()
                        ->scalarNode('secret_key')->defaultNull()->end()
                        ->scalarNode('eu_only')->defaultFalse()->end()
                        ->arrayNode('puzzle')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('global_endpoint')->defaultValue('https://api.friendlycaptcha.com/api/v1/puzzle')->end()
                                ->scalarNode('eu_endpoint')->defaultValue('https://eu-api.friendlycaptcha.eu/api/v1/puzzle')->end()
                            ->end()
                        ->end()
                        ->arrayNode('verification')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('global_endpoint')->defaultValue('https://api.friendlycaptcha.com/api/v1/siteverify')->end()
                                ->scalarNode('eu_endpoint')->defaultValue('https://eu-api.friendlycaptcha.eu/api/v1/siteverify')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cloudflare_turnstile')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('site_key')->defaultNull()->end()
                        ->scalarNode('secret_key')->defaultNull()->end()
                    ->end()
                ->end()

                ->arrayNode('email_checker')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('disposable_email_domains')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->booleanNode('include_subdomains')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end();

        return $rootNode;
    }

    private function buildFlagsNode(): NodeDefinition
    {
        $builder = new TreeBuilder('flags');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('use_custom_radio_checkbox')->defaultValue(true)->end()
                ->booleanNode('use_honeypot_field')->defaultValue(true)->end()
            ->end();

        return $rootNode;
    }

    private function createPersistenceNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('persistence');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->performNoDeepMerging()
            ->children()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity_manager')
                            ->info('Name of the entity manager that you wish to use for managing form builder entities.')
                            ->cannotBeEmpty()
                            ->defaultValue('default')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function buildFunnelNode(): NodeDefinition
    {
        $builder = new TreeBuilder('funnel');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->scalarNode('storage_provider')->defaultValue(SessionStorageProvider::class)->end()
                ->scalarNode('signal_storage_class')->defaultValue(FormDataSignalStorage::class)->end()
            ->end();

        return $rootNode;
    }

    private function buildDoubleOptInNode(): NodeDefinition
    {
        $builder = new TreeBuilder('double_opt_in');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->enumNode('redeem_mode')
                    ->values([DoubleOptInManager::REDEEM_MODE_DELETE, DoubleOptInManager::REDEEM_MODE_DEVALUE])
                    ->defaultValue(DoubleOptInManager::REDEEM_MODE_DELETE)
                ->end()
                ->arrayNode('expiration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('open_sessions')->info('Define expiration (in hours) for open sessions. 0 disables expiration.')->defaultValue(24)->end()
                        ->integerNode('redeemed_sessions')->info('Define expiration (in hours) for redeemed sessions. 0 disables expiration.')->defaultValue(0)->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

}

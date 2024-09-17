<?php

namespace FormBuilderBundle\DependencyInjection;

use FormBuilderBundle\Registry\ConditionalLogicRegistry;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use FormBuilderBundle\Configuration\Configuration as BundleConfiguration;

class FormBuilderExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        $container->setParameter('formbuilder.use_emailizr', $container->hasExtension('emailizr'));

        if ($container->hasExtension('twig') === false) {
            return;
        }

        $container->loadFromExtension('twig', [
            'globals' => [
                'form_builder_spam_protection_recaptcha_v3_site_key' => $config['spam_protection']['recaptcha_v3']['site_key'],
            ],
        ]);

        $this->buildEmailCheckerStack($container, $config);
    }

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../../config']));
        $loader->load('services.yaml');

        if ($config['double_opt_in']['enabled'] === true) {
            $loader->load('services/double_opt_in/services.yaml');
        }

        $conditionalLogicDefinition = $container->getDefinition(ConditionalLogicRegistry::class);

        foreach ($config['conditional_logic']['action'] as $identifier => $action) {
            $class = !is_null($action['class']) ? new Reference($action['class']) : null;
            unset($action['class']);
            $conditionalLogicDefinition->addMethodCall('register', [$identifier, $class, 'action', $action]);
        }

        foreach ($config['conditional_logic']['condition'] as $identifier => $condition) {
            $class = !is_null($condition['class']) ? new Reference($condition['class']) : null;
            unset($condition['class']);
            $conditionalLogicDefinition->addMethodCall('register', [$identifier, $class, 'condition', $condition]);
        }

        $configManagerDefinition = $container->getDefinition(BundleConfiguration::class);
        $configManagerDefinition->addMethodCall('setConfig', [$config]);

        $persistenceConfig = $config['persistence']['doctrine'];
        $entityManagerName = $persistenceConfig['entity_manager'];

        $container->setParameter('form_builder.persistence.doctrine.enabled', true);
        $container->setParameter('form_builder.persistence.doctrine.manager', $entityManagerName);

    }

    private function buildEmailCheckerStack(ContainerBuilder $container, array $config): void
    {
        $enabled = false;
        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../../config/optional']));

        if ($config['spam_protection']['email_checker']['disposable_email_domains']['enabled'] === true) {
            $enabled = true;
            $loader->load('services/disposable_email_domain_checker.yaml');
        } elseif (count($container->findTaggedServiceIds('form_builder.validator.email_checker')) > 0) {
            $enabled = true;
        }

        if ($enabled === false) {
            return;
        }

        $loader->load('config/email_checker.yaml');
        $loader->load('services/email_checker.yaml');
    }
}

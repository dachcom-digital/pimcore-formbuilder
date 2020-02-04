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
    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

        if ($container->hasExtension('twig') === false) {
            return;
        }

        $container->loadFromExtension('twig', [
            'globals' => [
                'form_builder_spam_protection_recaptcha_v3_site_key' => $config['spam_protection']['recaptcha_v3']['site_key'],
            ],
        ]);
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.yml');

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
    }
}

<?php

namespace DachcomBundle\Test\App;

use DachcomBundle\Test\App\Services\TestAdvancedDynamicChoices;
use DachcomBundle\Test\App\Services\TestSimpleDynamicChoices;
use Pimcore\Kernel;
use DachcomBundle\Test\DependencyInjection\MakeServicesPublicPass;
use DachcomBundle\Test\DependencyInjection\MonologChannelLoggerPass;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TestAppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundlesToCollection(BundleCollection $collection)
    {
        $collection->addBundle(new WebProfilerBundle());

        $bundleClass = getenv('DACHCOM_BUNDLE_CLASS');
        $collection->addBundle(new $bundleClass());

        if (class_exists('\\AppBundle\\AppBundle')) {
            $collection->addBundle(new \AppBundle\AppBundle());
        }

    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $runtimeConfigDir = codecept_data_dir() . 'config' . DIRECTORY_SEPARATOR;
            $loader = new YamlFileLoader($container, new FileLocator([$runtimeConfigDir]));
            $loader->load('config.yml');
        });
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MakeServicesPublicPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -100000);
        $container->addCompilerPass(new MonologChannelLoggerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);

        $definition = new Definition(TestSimpleDynamicChoices::class);
        $definition->setPublic(true);
        $definition->addTag('form_builder.dynamic_choice_builder', ['label' => 'Simple Test Selector']);
        $container->setDefinition(TestSimpleDynamicChoices::class, $definition);

        $definition = new Definition(TestAdvancedDynamicChoices::class);
        $definition->setPublic(true);
        $definition->addTag('form_builder.dynamic_choice_builder', ['label' => 'Advanced Test Selector']);
        $container->setDefinition(TestAdvancedDynamicChoices::class, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();
        \Pimcore::setKernel($this);
    }
}

<?php

namespace DachcomBundle\Test\App;

use DachcomBundle\Test\DependencyInjection\MakeServicesPublicPass;
use DachcomBundle\Test\DependencyInjection\MonologChannelLoggerPass;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Kernel;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestAppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $runtimeConfigDir = codecept_data_dir() . 'config' . DIRECTORY_SEPARATOR;
        $runtimeConfigDirConfig = $runtimeConfigDir . DIRECTORY_SEPARATOR . 'config.yml';

        $loader->load($runtimeConfigDirConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundlesToCollection(BundleCollection $collection)
    {
        if (class_exists('\\AppBundle\\AppBundle')) {
            $collection->addBundle(new \AppBundle\AppBundle());
        }

        $collection->addBundle(new WebProfilerBundle());

        $bundleClass = getenv('DACHCOM_BUNDLE_CLASS');
        $collection->addBundle(new $bundleClass());
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MakeServicesPublicPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION, -100000);
        $container->addCompilerPass(new MonologChannelLoggerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
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

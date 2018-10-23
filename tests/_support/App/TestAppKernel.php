<?php

namespace DachcomBundle\Test\App;

use Pimcore\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestAppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $bundleClass = getenv('DACHCOM_BUNDLE_HOME');
        $bundleName = getenv('DACHCOM_BUNDLE_NAME');
        $configName = getenv('DACHCOM_BUNDLE_CONFIG_FILE');

        if ($configName !== false) {
            \Codeception\Util\Debug::debug(sprintf('[%s] add custom config file %s', strtoupper($bundleName), $configName));
            $loader->load($bundleClass . '/etc/config/bundle/symfony/' . $configName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundlesToCollection(\Pimcore\HttpKernel\BundleCollection\BundleCollection $collection)
    {
        if (class_exists('\\AppBundle\\AppBundle')) {
            $collection->addBundle(new \AppBundle\AppBundle);
        }

        $bundleClass = getenv('DACHCOM_BUNDLE_CLASS');
        $collection->addBundle(new $bundleClass());
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new \DachcomBundle\Test\DependencyInjection\MakeServicesPublicPass(),
            \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, -100000);
        $container->addCompilerPass(new \DachcomBundle\Test\DependencyInjection\MonologChannelLoggerPass(),
            \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
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

<?php

namespace DachcomBundle\Test\Test;

use DachcomBundle\Test\Helper\PimcoreCore;
use DachcomBundle\Test\Util\FormHelper;
use Pimcore\Tests\Test\TestCase;

abstract class DachcomBundleTestCase extends TestCase
{
    /**
     * @var bool
     */
    protected $kernelHasCustomConfig = false;

    /**
     * Remove all forms before starting a single test
     */
    protected function _after()
    {
        FormHelper::removeAllForms();
        parent::_after();

        // config has changed, we need to restore default config before starting a new test!
        if ($this->kernelHasCustomConfig === true) {
            $this->getPimcoreBundle()->clearCache();
            $this->setSymfonyConfiguration(null);
            $this->kernelHasCustomConfig = false;
        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function generateSimpleFormForUnit()
    {
        return $this->getPimcoreBundle()->getContainer();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getPimcoreBundle()->getContainer();
    }

    /**
     * @return PimcoreCore
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getPimcoreBundle()
    {
        return $this->getModule('\\' . PimcoreCore::class);
    }

    protected function setSymfonyConfiguration($configuration)
    {
        $this->kernelHasCustomConfig = true;
        $this->getPimcoreBundle()->clearCache();
        $this->getPimcoreBundle()->bootKernelWithConfiguration($configuration);
    }

}

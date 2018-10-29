<?php

namespace DachcomBundle\Test\Test;

use DachcomBundle\Test\Helper\PimcoreCore;
use DachcomBundle\Test\Util\FormHelper;
use Pimcore\Tests\Test\TestCase;

abstract class DachcomBundleTestCase extends TestCase
{
    /**
     * Remove all forms before starting a single test
     */
    protected function _after()
    {
        FormHelper::removeAllForms();
        parent::_after();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     * @throws \Codeception\Exception\ModuleException
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
}

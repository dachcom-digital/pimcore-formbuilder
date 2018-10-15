<?php

namespace DachcomBundle\Test\Test;

use DachcomBundle\Test\Util\FormHelper;
use Pimcore\Tests\Test\TestCase;
use DachcomBundle\Test\Helper\PimcoreBundle;

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
     */
    protected function getContainer()
    {
        return $this->getPimcoreBundle()->getContainer();
    }

    /**
     * @return PimcoreBundle
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getPimcoreBundle()
    {
        return $this->getModule('\\' . PimcoreBundle::class);
    }
}

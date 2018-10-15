<?php

namespace DachcomBundle\Test\Unit;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Configuration\Configuration;
use Symfony\Component\HttpFoundation\Request;

class ActiveElementsTest extends DachcomBundleTestCase
{
    public function testActiveElements()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $adminConfig = $configuration->getConfig('admin');

        $this->assertArrayHasKey('active_elements', $adminConfig);
        $this->assertCount(0, $adminConfig['active_elements']['fields']);
    }

}

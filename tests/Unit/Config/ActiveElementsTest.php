<?php

namespace DachcomBundle\Test\Unit\Config;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use FormBuilderBundle\Configuration\Configuration;

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

<?php

namespace DachcomBundle\Test\Test;

use Dachcom\Codeception\Test\BundleTestCase;
use DachcomBundle\Test\Util\FormHelper;

abstract class DachcomBundleTestCase extends BundleTestCase
{
    protected function _after()
    {
        parent::_after();
        FormHelper::removeAllForms();
    }
}

<?php

namespace DachcomBundle\Test\Support\Test;

use Dachcom\Codeception\Support\Test\BundleTestCase;
use DachcomBundle\Test\Support\Util\FormHelper;

abstract class DachcomBundleTestCase extends BundleTestCase
{
    protected function _after()
    {
        parent::_after();
        FormHelper::removeAllForms();
    }
}

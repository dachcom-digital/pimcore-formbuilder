<?php

namespace DachcomBundle\Test\unit\SimpleForm;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormManager;

class FormStorageTest extends DachcomBundleTestCase
{
    public function testFormConfig()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $formConfig = $form->getConfig();

        $this->assertInternalType('string', $formConfig['action']);
        $this->assertEquals('/', $formConfig['action']);

        $this->assertInternalType('string', $formConfig['method']);
        $this->assertEquals('POST', $formConfig['method']);

        $this->assertInternalType('string', $formConfig['enctype']);
        $this->assertEquals('multipart/form-data', $formConfig['enctype']);

        $this->assertInternalType('boolean', $formConfig['noValidate']);
        $this->assertEquals(true, $formConfig['noValidate']);

        $this->assertInternalType('boolean', $formConfig['useAjax']);
        $this->assertEquals(false, $formConfig['useAjax']);
    }

}

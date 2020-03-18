<?php

namespace DachcomBundle\Test\unit\Form;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Storage\Form;

class FormFactoryWithDataTest extends DachcomBundleTestCase
{
    public function setUp()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $manager->save($testFormBuilder->build());
    }

    public function testFormGetterById()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);
        $form = $factory->getFormById(1);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(1, $form->getId());
    }

    public function testFormGetterIdByName()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);
        $form = $factory->getFormIdByName('TEST_FORM');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(1, $form->getId());
    }

    public function testFormGetAll()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $forms = $factory->getAllForms();
        $this->assertCount(1, $forms);
    }

}

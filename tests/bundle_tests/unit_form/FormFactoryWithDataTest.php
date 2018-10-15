<?php

namespace DachcomBundle\Test\Unit;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Factory\FormFactoryInterface;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\Form;

class FormFactoryWithDataTest extends DachcomBundleTestCase
{
    public function setUp()
    {
        $manager = $this->getContainer()->get(FormManager::class);
        $manager->save(FormHelper::generateSimpleForm('TEST_FORM'));
    }

    public function testFormGetterById()
    {
        $factory = $this->getContainer()->get(FormFactoryInterface::class);
        $form = $factory->getFormById(1);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(1, $form->getId());
    }

    public function testFormGetterIdByName()
    {
        $factory = $this->getContainer()->get(FormFactoryInterface::class);
        $form = $factory->getFormIdByName('TEST_FORM');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(1, $form->getId());
    }

    public function testFormGetAll()
    {
        $factory = $this->getContainer()->get(FormFactoryInterface::class);

        $forms = $factory->getAllForms();
        $this->assertCount(1, $forms);
    }

}

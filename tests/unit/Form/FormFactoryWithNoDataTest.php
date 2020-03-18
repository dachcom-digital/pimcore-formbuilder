<?php

namespace DachcomBundle\Test\unit\Form;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;
use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormField;

class FormFactoryWithNoDataTest extends DachcomBundleTestCase
{
    public function testFormCreation()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $form = $factory->createForm();
        $this->assertInstanceOf(Form::class, $form);
    }

    public function testFormGetterById()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);
        $form = $factory->getFormById(99);
        $this->assertNull($form);
    }

    public function testFormGetterIdByName()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);
        $form = $factory->getFormIdByName('form');
        $this->assertNull($form);
    }

    public function testFormGetAll()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $forms = $factory->getAllForms();
        $this->assertCount(0, $forms);
    }

    public function testFormFieldCreation()
    {
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $form = $factory->createFormField();
        $this->assertInstanceOf(FormField::class, $form);
    }

}

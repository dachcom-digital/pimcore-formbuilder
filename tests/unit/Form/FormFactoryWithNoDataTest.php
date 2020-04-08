<?php

namespace DachcomBundle\Test\unit\Form;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;

class FormFactoryWithNoDataTest extends DachcomBundleTestCase
{
    public function testFormDefinitionCreation()
    {
        /** @var FormDefinitionFactoryInterface $factory */
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $form = $factory->createFormDefinition();
        $this->assertInstanceOf(FormDefinitionInterface::class, $form);
    }

    public function testFormFieldContainerDefinitionCreation()
    {
        /** @var FormDefinitionFactoryInterface $factory */
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $form = $factory->createFormFieldContainerDefinition();
        $this->assertInstanceOf(FormFieldContainerDefinitionInterface::class, $form);
    }

    public function testFormFieldDefinitionCreation()
    {
        /** @var FormDefinitionFactoryInterface $factory */
        $factory = $this->getContainer()->get(FormDefinitionFactoryInterface::class);

        $form = $factory->createFormFieldDefinition();
        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $form);
    }
}

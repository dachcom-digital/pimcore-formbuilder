<?php

namespace DachcomBundle\Test\unit\SimpleForm;

use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;

class FormFieldContainerStorageTest extends DachcomBundleTestCase
{
    /**
     * @throws \Exception
     */
    public function testNewFormFieldContainerRepeaterField()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $testFormBuilder->addFormFieldContainer(
            'repeater',
            'repeater_container',
            [
                'min' => 1
            ],
            [
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field',
                    'display_name' => 'Text Field',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ]
            ]
        );

        $form = $manager->save($testFormBuilder->build());
        /** @var FormFieldContainerDefinitionInterface $field */
        $field = $form->getField('repeater_container');

        $this->assertInstanceOf(FormFieldContainerDefinitionInterface::class, $field);
        $this->assertEquals('repeater_container', $field->getName());
        $this->assertEquals('container', $field->getType());
        $this->assertEquals('repeater', $field->getSubType());
        $this->assertInternalType('array', $field->getConfiguration());
        $this->assertInternalType('array', $field->getFields());
        $this->assertCount(1, $field->getFields());
        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field->getFields()[0]);

    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldContainerFieldsetField()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $testFormBuilder->addFormFieldContainer(
            'fieldset',
            'repeater_container',
            [
                'min' => 1
            ],
            [
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field',
                    'display_name' => 'Text Field',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ]
            ]
        );

        $form = $manager->save($testFormBuilder->build());
        /** @var FormFieldContainerDefinitionInterface $field */
        $field = $form->getField('repeater_container');

        $this->assertInstanceOf(FormFieldContainerDefinitionInterface::class, $field);
        $this->assertEquals('repeater_container', $field->getName());
        $this->assertEquals('container', $field->getType());
        $this->assertEquals('fieldset', $field->getSubType());
        $this->assertInternalType('array', $field->getConfiguration());
        $this->assertInternalType('array', $field->getFields());
        $this->assertCount(1, $field->getFields());
        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field->getFields()[0]);
    }
}

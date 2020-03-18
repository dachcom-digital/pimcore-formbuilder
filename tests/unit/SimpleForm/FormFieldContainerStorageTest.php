<?php

namespace DachcomBundle\Test\unit\SimpleForm;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Storage\FormField;
use FormBuilderBundle\Storage\FormFieldContainer;
use FormBuilderBundle\Storage\FormFieldContainerInterface;

class FormFieldContainerStorageTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
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
        /** @var FormFieldContainerInterface $field */
        $field = $form->getField('repeater_container');

        $this->assertInstanceOf(FormFieldContainer::class, $field);
        $this->assertEquals('repeater_container', $field->getName());
        $this->assertEquals('container', $field->getType());
        $this->assertEquals('repeater', $field->getSubType());
        $this->assertInternalType('array', $field->getConfiguration());
        $this->assertInternalType('array', $field->getFields());
        $this->assertCount(1, $field->getFields());
        $this->assertInstanceOf(FormField::class, $field->getFields()[0]);

    }

    /**
     * @throws \Codeception\Exception\ModuleException
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
        /** @var FormFieldContainerInterface $field */
        $field = $form->getField('repeater_container');

        $this->assertInstanceOf(FormFieldContainer::class, $field);
        $this->assertEquals('repeater_container', $field->getName());
        $this->assertEquals('container', $field->getType());
        $this->assertEquals('fieldset', $field->getSubType());
        $this->assertInternalType('array', $field->getConfiguration());
        $this->assertInternalType('array', $field->getFields());
        $this->assertCount(1, $field->getFields());
        $this->assertInstanceOf(FormField::class, $field->getFields()[0]);
    }
}

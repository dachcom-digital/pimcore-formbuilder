<?php

namespace DachcomBundle\Test\Unit\SimpleForm;

use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;

class FormFieldStorageTest extends DachcomBundleTestCase
{
    /**
     * @throws \Exception
     */
    public function testNewFormFieldSimpleDropDown()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_dropdown');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('simple_dropdown', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldInput1()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_1');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('simple_text_input_1', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldInput2()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_2');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('simple_text_input_2', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldInput3()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_3');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('simple_text_input_3', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldInput4()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_4');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('simple_text_input_4', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldSingleCheckbox()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('single_checkbox');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('single_checkbox', $field->getName());
        $this->assertEquals('checkbox', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldCheckboxes()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('checkboxes');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('checkboxes', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldRadios()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('radios');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('radios', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldTextArea()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_area');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('simple_text_area', $field->getName());
        $this->assertEquals('textarea', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testNewFormFieldSubmitButton()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('submit');

        $this->assertInstanceOf(FormFieldDefinitionInterface::class, $field);
        $this->assertEquals('submit', $field->getName());
        $this->assertEquals('submit', $field->getType());
        $this->assertIsArray($field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    /**
     * @throws \Exception
     */
    public function testFormFieldOptions()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $testFormBuilder->addFormFieldInput('input_with_options', ['class' => 'my-shiny-class']);
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('input_with_options');
        $options = $field->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('class', $options);
        $this->assertEquals('my-shiny-class', $options['class']);
    }

    /**
     * @throws \Exception
     */
    public function testFormFieldOptionals()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $testFormBuilder->addFormFieldInput('input_with_optional', [], ['template' => 'default']);
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('input_with_optional');
        $optionals = $field->getOptional();

        $this->assertIsArray($optionals);
        $this->assertArrayHasKey('template', $optionals);
        $this->assertEquals('default', $optionals['template']);
    }
}

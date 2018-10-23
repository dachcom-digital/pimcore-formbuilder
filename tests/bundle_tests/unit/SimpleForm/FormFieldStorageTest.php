<?php

namespace DachcomBundle\Test\unit\SimpleForm;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\FormField;

class FormFieldStorageTest extends DachcomBundleTestCase
{
    public function testNewFormFieldSimpleDropDown()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_dropdown');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simple_dropdown', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    public function testNewFormFieldInput1()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_1');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simple_text_input_1', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldInput2()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_2');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simple_text_input_2', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldInput3()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_3');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simple_text_input_3', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldInput4()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_input_4');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simple_text_input_4', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldSingleCheckbox()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('single_checkbox');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('single_checkbox', $field->getName());
        $this->assertEquals('checkbox', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldCheckboxes()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('checkboxes');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('checkboxes', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    public function testNewFormFieldRadios()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('radios');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('radios', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    public function testNewFormFieldTextArea()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('simple_text_area');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simple_text_area', $field->getName());
        $this->assertEquals('textarea', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldSubmitButton()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $field = $form->getField('submit');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('submit', $field->getName());
        $this->assertEquals('submit', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }
}

<?php

namespace DachcomBundle\Test\Unit;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\FormField;

class FormFieldStorageTest extends DachcomBundleTestCase
{
    public function testNewFormFieldSalutation()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('salutation');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('salutation', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    public function testNewFormFieldPreName()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('prename');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('prename', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldLastName()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('lastname');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('lastname', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldPhone()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('phone');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('phone', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldEmail()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('email');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('email', $field->getName());
        $this->assertEquals('text', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldCheckbox()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('checkbox');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('checkbox', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    public function testNewFormFieldRadio()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('radios');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('radios', $field->getName());
        $this->assertEquals('choice', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(5, $field->getOptions());
    }

    public function testNewFormFieldComment()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('comment');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('comment', $field->getName());
        $this->assertEquals('textarea', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }

    public function testNewFormFieldSubmitButton()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $form = $manager->save(FormHelper::generateSimpleForm());
        $field = $form->getField('send');

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('send', $field->getName());
        $this->assertEquals('submit', $field->getType());
        $this->assertInternalType('array', $field->getOptions());
        $this->assertCount(1, $field->getOptions());
    }
}

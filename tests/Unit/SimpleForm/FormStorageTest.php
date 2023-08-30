<?php

namespace DachcomBundle\Test\Unit\SimpleForm;

use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Manager\FormDefinitionManager;

class FormStorageTest extends DachcomBundleTestCase
{
    /**
     * @throws \Exception
     */
    public function testFormConfig()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $formConfig = $form->getConfiguration();

        $this->assertIsString($formConfig['action']);
        $this->assertEquals('/', $formConfig['action']);

        $this->assertIsString($formConfig['method']);
        $this->assertEquals('POST', $formConfig['method']);

        $this->assertIsString($formConfig['enctype']);
        $this->assertEquals('multipart/form-data', $formConfig['enctype']);

        $this->assertIsBool($formConfig['noValidate']);
        $this->assertEquals(true, $formConfig['noValidate']);

        $this->assertIsBool($formConfig['useAjax']);
        $this->assertEquals(false, $formConfig['useAjax']);
    }

    /**
     * @throws \Exception
     */
    public function testFormNameConfig()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $this->assertEquals('TEST_FORM', $form->getName());
    }

    /**
     * @throws \Exception
     */
    public function testFormGroupEmptyConfig()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $this->assertEquals(null, $form->getGroup());
    }

    /**
     * @throws \Exception
     */
    public function testFormGroupPopulatedConfig()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $testFormBuilder->setGroup('group1');
        $form = $manager->save($testFormBuilder->build());

        $this->assertEquals('group1', $form->getGroup());
    }

    /**
     * @throws \Exception
     */
    public function testFormAttribute()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $testFormBuilder->addFormAttributes('class', 'my-shiny-class');
        $form = $manager->save($testFormBuilder->build());

        $formConfig = $form->getConfiguration();

        $this->assertIsArray($formConfig['attributes']);

        $attributeIndex = array_search('class', array_column($formConfig['attributes'], 'option'));

        $this->assertNotFalse($attributeIndex);
        $this->assertEquals('my-shiny-class', $formConfig['attributes'][$attributeIndex]['value']);
    }

    /**
     * @throws \Exception
     */
    public function testFormMultipleAttributes()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $testFormBuilder->addFormAttributes('class', 'my-shiny-class');
        $testFormBuilder->addFormAttributes('maxlength', 30);
        $form = $manager->save($testFormBuilder->build());

        $formConfig = $form->getConfiguration();

        $this->assertIsArray($formConfig['attributes']);

        $attribute1Index = array_search('class', array_column($formConfig['attributes'], 'option'));
        $this->assertNotFalse($attribute1Index);
        $this->assertEquals('my-shiny-class', $formConfig['attributes'][$attribute1Index]['value']);

        $attribute2Index = array_search('maxlength', array_column($formConfig['attributes'], 'option'));
        $this->assertNotFalse($attribute2Index);
        $this->assertEquals(30, $formConfig['attributes'][$attribute2Index]['value']);
    }

    /**
     * @throws \Exception
     */
    public function testFormMetaCreationDate()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $date1 = $form->getCreationDate();
        $date2 = new \DateTime();

        $this->assertInstanceOf(\DateTime::class, $form->getCreationDate());
        $this->assertEquals($date2->format('d.m.Y'), $date1->format('d.m.Y'));
    }

    /**
     * @throws \Exception
     */
    public function testFormMetaCreationDateHasNotChangedAfterUpdating()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $date1 = $form->getCreationDate();

        sleep(2);

        $updatedForm = $manager->save($testFormBuilder->build(), $form->getId());
        $date2 = $updatedForm->getCreationDate();

        $this->assertEquals($date1->format('d.m.Y H:i:s'), $date2->format('d.m.Y H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function testFormMetaModificationDate()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $date1 = $form->getModificationDate();
        $date2 = new \DateTime();

        $this->assertInstanceOf(\DateTime::class, $form->getModificationDate());
        $this->assertEquals($date2->format('d.m.Y'), $date1->format('d.m.Y'));
    }

    /**
     * @throws \Exception
     */
    public function testFormMetaModificationDateHasChangedAfterUpdating()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $date1 = $form->getModificationDate();

        sleep(2);

        $updatedForm = $manager->save($testFormBuilder->build(), $form->getId());
        $date2 = $updatedForm->getModificationDate();

        $this->assertNotEquals($date1->format('d.m.Y H:i:s'), $date2->format('d.m.Y H:i:s'));
    }

    /**
     * @throws \Exception
     */
    public function testFormMetaCreatedBy()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $createdById = $form->getCreatedBy();

        $this->assertIsInt($createdById);
        $this->assertEquals(0, $createdById);
    }

    /**
     * @throws \Exception
     */
    public function testFormMetaModifiedBy()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $form = $manager->save($testFormBuilder->build());

        $createdById = $form->getModifiedBy();

        $this->assertIsInt($createdById);
        $this->assertEquals(0, $createdById);
    }
}

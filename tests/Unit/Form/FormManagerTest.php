<?php

namespace DachcomBundle\Test\Unit\Form;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Support\Util\FormHelper;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormManagerTest extends DachcomBundleTestCase
{
    public function testSaveNewForm()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $this->assertInstanceOf(FormDefinitionInterface::class, $form);
    }

    public function testDeleteForm()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $formId = $form->getId();
        $manager->delete($formId);
        $form = $manager->getById($formId);
        $this->assertNull($form);
    }

    public function testSaveNewFormId()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $this->assertEquals(1, $form->getId());
    }

    public function testRenameForm()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $renamedForm = $manager->rename($form->getId(), 'MOCK_NEW_FORM');
        $this->assertEquals('MOCK_NEW_FORM', $renamedForm->getName());
    }

    public function testFormGetterById()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $form = $manager->getById(99);
        $this->assertNull($form);
    }

    public function testFormGetterIdByName()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $form = $manager->getIdByName('form');
        $this->assertNull($form);
    }

    public function testFormGetAll()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $forms = $manager->getAll();
        $this->assertCount(0, $forms);
    }
}

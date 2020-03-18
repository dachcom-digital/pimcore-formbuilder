<?php

namespace DachcomBundle\Test\unit\Form;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Storage\Form;

class FormManagerTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testSaveNewForm()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testDeleteForm()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $manager->delete($form->getId());
        $form = $manager->getById($form->getId());
        $this->assertNull($form);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testSaveNewFormId()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $this->assertEquals(1, $form->getId());
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testRenameForm()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $renamedForm = $manager->rename($form->getId(), 'MOCK_NEW_FORM');
        $this->assertEquals('MOCK_NEW_FORM', $renamedForm->getName());
    }
}

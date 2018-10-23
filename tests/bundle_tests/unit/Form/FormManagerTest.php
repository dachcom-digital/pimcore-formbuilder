<?php

namespace DachcomBundle\Test\unit\Form;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\Form;

class FormManagerTest extends DachcomBundleTestCase
{
    public function testSaveNewForm()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $this->assertInstanceOf(Form::class, $form);
    }

    public function testDeleteForm()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $manager->delete($form->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Form with id: %s doesn\'t exist', $form->getId()));

        $manager->getById($form->getId());
    }

    public function testSaveNewFormId()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());
        $this->assertEquals(1, $form->getId());
    }

    public function testRenameForm()
    {
        $manager = $this->getContainer()->get(FormManager::class);

        $testFormBuilder = FormHelper::generateSimpleForm();
        $form = $manager->save($testFormBuilder->build());

        $renamedForm = $manager->rename($form->getId(), 'MOCK_NEW_FORM');
        $this->assertEquals('MOCK_NEW_FORM', $renamedForm->getName());
    }
}

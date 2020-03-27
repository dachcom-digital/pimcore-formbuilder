<?php

namespace DachcomBundle\Test\unit\Form;

use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormManagerWithDataTest extends DachcomBundleTestCase
{
    public function setUp()
    {
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $testFormBuilder = FormHelper::generateSimpleForm('TEST_FORM');
        $manager->save($testFormBuilder->build());
    }

    public function testFormGetterById()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $form = $manager->getById(1);

        $this->assertInstanceOf(FormDefinitionInterface::class, $form);
        $this->assertEquals(1, $form->getId());
    }

    public function testFormGetterIdByName()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $form = $manager->getIdByName('TEST_FORM');

        $this->assertInstanceOf(FormDefinitionInterface::class, $form);
        $this->assertEquals(1, $form->getId());
    }

    public function testFormGetAll()
    {
        /** @var FormDefinitionManager $manager */
        $manager = $this->getContainer()->get(FormDefinitionManager::class);
        $forms = $manager->getAll();

        $this->assertCount(1, $forms);
    }
}

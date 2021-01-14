<?php

namespace DachcomBundle\Test\acceptance\Container;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class ContainerRepeaterCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerRepeaterBlockAddition(AcceptanceTester $I)
    {
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascript']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'label'              => 'Repeater Container',
                    'label_add_block'    => 'Add New',
                    'label_remove_block' => 'Remove',
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
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);
        $formTemplate = 'bootstrap_4_layout.html.twig';
        $subSelector1 = 'input#formbuilder_1_repeater_container_0_sub_text_field';
        $subSelector2 = 'input#formbuilder_1_repeater_container_1_sub_text_field';
        $repeaterFieldSelector = $testFormBuilder->getFormFieldSelector(1, 'repeater_container');

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->waitForElement('a.add-block', 5);

        $I->click('Add New', $repeaterFieldSelector);
        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector1));

        $I->click('Add New', $testFormBuilder->getFormSelector(1));
        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector2));
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerRepeaterBlockMaxAddition(AcceptanceTester $I)
    {
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascript']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'label'              => 'Repeater Container',
                    'label_add_block'    => 'Add New',
                    'label_remove_block' => 'Remove',
                    'max'                => 2
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
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);
        $formTemplate = 'bootstrap_4_layout.html.twig';
        $subSelector1 = 'input#formbuilder_1_repeater_container_0_sub_text_field';
        $subSelector2 = 'input#formbuilder_1_repeater_container_1_sub_text_field';
        $repeaterFieldSelector = $testFormBuilder->getFormFieldSelector(1, 'repeater_container');

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->waitForElement('a.add-block', 5);

        $I->click('Add New', $testFormBuilder->getFormSelector(1));
        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector1));

        $I->click('Add New', $repeaterFieldSelector);
        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector2));
        $I->waitforElementNotVisible(sprintf('%s %s', $repeaterFieldSelector, 'a.add-block'));

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerRepeaterBlockRemoval(AcceptanceTester $I)
    {
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascript']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'label'              => 'Repeater Container',
                    'label_add_block'    => 'Add New',
                    'label_remove_block' => 'Remove',
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
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);
        $formTemplate = 'bootstrap_4_layout.html.twig';
        $subSelector = 'input#formbuilder_1_repeater_container_0_sub_text_field';
        $repeaterFieldSelector = $testFormBuilder->getFormFieldSelector(1, 'repeater_container');

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->waitForElement('a.add-block', 5);

        $I->click('Add New', $testFormBuilder->getFormSelector(1));
        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector));
        $I->waitForElement('a.remove-block', 5);

        $I->click('Remove', sprintf('%s %s', $repeaterFieldSelector, '.formbuilder-container-block'));
        $I->waitforElementNotVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector));
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerRepeaterBlockMinRemoval(AcceptanceTester $I)
    {
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascript']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'label'              => 'Repeater Container',
                    'label_add_block'    => 'Add New',
                    'label_remove_block' => 'Remove',
                    'min'                => 1
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
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);
        $formTemplate = 'bootstrap_4_layout.html.twig';
        $subSelector1 = 'input#formbuilder_1_repeater_container_0_sub_text_field';
        $subSelector2 = 'input#formbuilder_1_repeater_container_1_sub_text_field';
        $repeaterFieldSelector = $testFormBuilder->getFormFieldSelector(1, 'repeater_container');

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->waitForElement('a.add-block', 5);

        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector1));
        $I->waitforElementNotVisible(sprintf('%s %s', $repeaterFieldSelector, '.remove-block'));

        $I->click('Add New', $testFormBuilder->getFormSelector(1));
        $I->waitforElementVisible(sprintf('%s %s', $repeaterFieldSelector, $subSelector2));
        $I->waitForElement('a.remove-block', 5);

        $I->click('Remove', sprintf('%s %s', $repeaterFieldSelector, '.formbuilder-container-block:nth-child(2)'));
        $I->waitforElementNotVisible(sprintf('%s %s', $repeaterFieldSelector, '.formbuilder-container-block:first-child .remove-block'));
    }
}

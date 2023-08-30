<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

/**
 * Action "toggleClass". Must work on:
 * - all fields
 */
class ElementToggleClassActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['simple_text_input_2'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '.test-class'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsToggleClassOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '.test-class', ''),
            5
        );

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '.test-class'),
            5
        );

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', '.test-class'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnSimpleTextArea(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['simple_text_area'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_area', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_text_area', '.test-class'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnMultipleSelect(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['multiple_select'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'multiple_select', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'multiple_select', '.test-class'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['simple_dropdown'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_dropdown', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'simple_dropdown', '.test-class'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnRadios(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['radios'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'radios', '.test-class', '_0'),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'radios', '.test-class', '_0'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnCheckboxes(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['checkboxes'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'checkboxes', '.test-class', '_0'),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'checkboxes', '.test-class', '_0'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnSimpleCheckbox(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['single_checkbox'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'single_checkbox', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'single_checkbox', '.test-class'),
            5
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleClassOnContainer(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleClass',
            'fields' => ['repeater_container'],
            'class'  => 'test-class'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], function (TestFormBuilder $testFormBuilder) {
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
            return $testFormBuilder;
        });

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible(
            $testFormBuilder->getFormFieldSelector(1, 'repeater_container', '.test-class', ''),
            5
        );

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementNotVisible(
            $testFormBuilder->getFormFieldSelector(1, 'repeater_container', '.test-class'),
            5
        );
    }

}

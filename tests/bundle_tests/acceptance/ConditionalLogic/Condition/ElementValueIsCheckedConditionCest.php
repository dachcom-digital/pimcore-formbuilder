<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

/**
 * Condition "is_checked". Must work on:
 * - single checkbox
 * - choice: default | extended | multiple | extended & multiple
 *
 * Triggers on those elements regardless its value.
 * Only undefined and empty values will be ignored
 */
class ElementValueIsCheckedConditionCest extends AbstractConditionCest
{
    protected $action = [
        'type'   => 'toggleElement',
        'fields' => ['simple_text_input_1'],
        'state'  => 'hide'
    ];

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueCheckedOnSingleCheckbox(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['single_checkbox'],
            'comparator' => 'is_checked',
            'value'      => ''
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->checkOption($formBuilder->getFormFieldSelector(1, 'single_checkbox'));
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueCheckedOnCheckboxes(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'is_checked',
            'value'      => ''
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->checkOption($formBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'));
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueCheckedOnMultipleSelect(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'is_checked',
            'value'      => ''
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'multiple_select'), ['select_2', 'select_3']);
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueCheckedOnRadios(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_checked',
            'value'      => ''
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'radios', '', '_3'), 'radio3');
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueCheckedOnSimpleDropDown(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_checked',
            'value'      => ''
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueCheckedOnSimpleDropDownWithPlaceHolder(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_checked',
            'value'      => ''
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition, function (TestFormBuilder $formBuilder) {
            // re-add field with placeholder functionality.
            $formBuilder
                ->removeField('simple_dropdown')
                ->addFormFieldChoice('simple_dropdown',
                    [
                        'Simple DropDown Value 0' => 'simple_drop_down_value_0',
                        'Simple DropDown Value 1' => 'simple_drop_down_value_1'
                    ],
                    ['placeholder' => 'Please Select']
                );

            return $formBuilder;

        });

        $I->waitForElementNotVisible($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }
}

<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 * Condition "is_not_checked". Must work on:
 * - single checkbox
 * - choice: default (dropDown) | extended (radio) | multiple (multiSelect) | extended & multiple (checkboxes)
 *
 * Triggers on those elements regardless its value.
 * Only undefined and empty values will be ignored
 */
class ElementValueIsNotCheckedConditionCest extends AbstractConditionCest
{
    protected $actions = [
        [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_1'],
            'state'  => 'hide'
        ]
    ];

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnNotCheckedSingleCheckbox(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['single_checkbox'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnCheckedSingleCheckbox(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['single_checkbox'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'single_checkbox'));
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnCheckboxesWithNoSelectedElements(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnCheckboxesWithSelectedElements(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'));
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnMultipleSelectWithNoSelectedElements(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnMultipleSelectWithElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), ['select_2', 'select_3']);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnRadiosWithNoElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnRadiosWithElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2'), 'radio2');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueNotCheckedOnSimpleDropDownWithPlaceholder(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_not_checked',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            // re-add field with placeholder functionality.
            $testFormBuilder
                ->removeField('simple_dropdown')
                ->addFormFieldChoice('simple_dropdown',
                    [
                        'Simple DropDown Value 0' => 'simple_drop_down_value_0',
                        'Simple DropDown Value 1' => 'simple_drop_down_value_1'
                    ],
                    ['placeholder' => 'Please Select']
                );

            return $testFormBuilder;

        });

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

}

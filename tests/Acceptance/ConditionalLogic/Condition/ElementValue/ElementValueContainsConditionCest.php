<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\Support\AcceptanceTester;
use DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 *  * Condition "contains". Must work on:
 * - single checkbox
 * - inputs
 * - choice: default (dropDown) | extended (radio) | multiple (multiSelect) | extended & multiple (checkboxes)
 */
class ElementValueContainsConditionCest extends AbstractConditionCest
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
    public function testElementValueContainsOnNotEmptySimpleInputWithWhiteSpaceInConditionValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_2'],
            'comparator' => 'contains',
            'value'      => 'text1, text2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');
        // just to trigger javascript blur we need to fill another field (text3)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'text3');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnNotEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_2'],
            'comparator' => 'contains',
            'value'      => 'text1,text2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text1');
        // just to trigger javascript blur we need to fill another field (text3)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'text3');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnSimpleInputWithMoreTextThanExpected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_2'],
            'comparator' => 'contains',
            'value'      => 'text1,text2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'there is text2 but is still not valid');
        // just to trigger javascript blur we need to fill another field (text3)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'text3');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnCheckedSingleCheckbox(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['single_checkbox'],
            'comparator' => 'contains',
            'value'      => '1'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'single_checkbox'));
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnNotCheckedSingleCheckbox(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['single_checkbox'],
            'comparator' => 'contains',
            'value'      => '1'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnCheckboxesWithNoValidElementChecked(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'contains',
            'value'      => 'check3,check4'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnCheckboxesWithOneValidElementChecked(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'contains',
            'value'      => 'check1,check2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnCheckboxesWithTwoValidElementsChecked(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'contains',
            'value'      => 'check1,check2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'));
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'contains',
            'value'      => 'simple_drop_down_value_1'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'), 'simple_drop_down_value_1');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnSimpleDropDownWithPlaceHolder(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'contains',
            'value'      => 'simple_drop_down_value_0'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'), 'simple_drop_down_value_1');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnMultipleSelectWithOneValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'contains',
            'value'      => 'select_1,select_2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), 'select_1');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnMultipleSelectWithNoValidElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'contains',
            'value'      => 'select_2,select_3'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), 'select_1');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnMultipleSelectWithTwoValidElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'contains',
            'value'      => 'select_1,select_2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), ['select_2', 'select_3']);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnRadiosWithValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'contains',
            'value'      => 'radio1,radio3'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), 'radio3');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnRadiosWithNoValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'contains',
            'value'      => 'radio1,radio3'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2'), 'radio2');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }
}

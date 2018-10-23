<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

use DachcomBundle\Test\AcceptanceTester;

/**
 *  * Condition "contains". Must work on:
 * - single checkbox
 * - inputs
 * - choice: default | extended | multiple | extended & multiple
 */
class ElementValueContainsConditionCest extends AbstractConditionCest
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
    public function testElementValueContainsOnSimpleInputWithWhiteSpace(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_2'],
            'comparator' => 'contains',
            'value'      => 'text1, text2'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->fillField($formBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');
        // just to trigger javascript blur we need to fill another field (text3)
        $I->fillField($formBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'text3');
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnSimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_2'],
            'comparator' => 'contains',
            'value'      => 'text1,text2'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->fillField($formBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text1');
        // just to trigger javascript blur we need to fill another field (text3)
        $I->fillField($formBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'text3');
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnSingleCheckbox(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['single_checkbox'],
            'comparator' => 'contains',
            'value'      => '1'
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
    public function testElementValueContainsOnCheckboxesWithOneElementChecked(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'contains',
            'value'      => 'check1,check2'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->checkOption($formBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueDoesNotContainsOnCheckboxesWithOneElementChecked(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'contains',
            'value'      => 'check3,check4'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->checkOption($formBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->waitForElementNotVisible($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnCheckboxesWithTwoElementsChecked(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['checkboxes'],
            'comparator' => 'contains',
            'value'      => 'check1,check2'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->checkOption($formBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->checkOption($formBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'));
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnSimpleDropDown(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'contains',
            'value'      => 'simple_drop_down_value_1'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'simple_dropdown'), 'simple_drop_down_value_1');
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueDoesNotContainOnSimpleDropDown(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'contains',
            'value'      => 'simple_drop_down_value_0'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'simple_dropdown'), 'simple_drop_down_value_1');
        $I->waitForElementNotVisible($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnMultipleSelectWithOneValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'contains',
            'value'      => 'select_1,select_2'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'multiple_select'), 'select_1');
        $I->waitForElement($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueDoesNotContainsOnMultipleSelectWithOneValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'contains',
            'value'      => 'select_2,select_3'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'multiple_select'), 'select_1');
        $I->waitForElementNotVisible($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueContainsOnMultipleSelectWithTwoValuesSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'contains',
            'value'      => 'select_1,select_2'
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
    public function testElementValueContainsOnRadios(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'contains',
            'value'      => 'radio1,radio3'
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
    public function testElementValueDoesNotContainsOnRadios(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'contains',
            'value'      => 'radio1,radio3'
        ];

        $formBuilder = $this->runTestWithCondition($I, $condition);

        $I->selectOption($formBuilder->getFormFieldSelector(1, 'radios', '', '_2'), 'radio2');
        $I->waitForElementNotVisible($formBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }
}

<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 * Condition "is_empty_value". Must work on:
 * - inputs
 * - textareas
 * - choice: default (dropDown) | extended (radio) | multiple (multiSelect)
 */
class ElementValueIsEmptyValueConditionCest extends AbstractConditionCest
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
    public function testElementValueEmptyOnEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_empty_value',
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
    public function testElementValueEmptyOnNotEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_3'],
            'comparator' => 'is_empty_value',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'text1');
        // just to trigger javascript blur we need to fill another field
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueEmptyOnTextArea(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_empty_value',
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
    public function testElementValueEmptyOnNotEmptyTextArea(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_empty_value',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), 'text area text');
        // just to trigger javascript blur we need to fill another field
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueEmptyOnSimpleDropDownWithFirstValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_empty_value',
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
    public function testElementValueEmptyOnSimpleDropDownWithPlaceHolder(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_empty_value',
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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueEmptyOnRadiosWithNoElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_empty_value',
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
    public function testElementValueEmptyOnRadiosWithElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_empty_value',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), 'radio3');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementValueEmptyOnMultipleSelectWithNoElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'is_empty_value',
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
    public function testElementValueEmptyOnMultipleSelectWithElementsSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['multiple_select'],
            'comparator' => 'is_empty_value',
            'value'      => ''
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), ['select_2', 'select_3']);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

}

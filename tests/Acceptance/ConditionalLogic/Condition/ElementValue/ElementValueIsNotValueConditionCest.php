<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 * Condition "is_not_value". Must work on:
 * - inputs
 * - textareas
 * - choice: default (dropDown) | extended (radio)
 */
class ElementValueIsNotValueConditionCest extends AbstractConditionCest
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
    public function testElementIsNotValueOnEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_not_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnSimpleInputWithNotValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_3'],
            'comparator' => 'is_not_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'TEXT');
        // just to trigger javascript blur we need to fill another field
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'), 'text2');

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnSimpleInputWithValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_3'],
            'comparator' => 'is_not_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 'OTHER TEXT');
        // just to trigger javascript blur we need to fill another field
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnEmptyTextArea(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_not_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnTextAreaWithNotValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_not_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), 'TEXT');
        // just to trigger javascript blur we need to fill another field
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnTextAreaWithValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_not_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), 'OTHER TEXT');
        // just to trigger javascript blur we need to fill another field
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnSimpleDropDownWithValidFirstValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_not_value',
            'value'      => 'simple_drop_down_value_2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnSimpleDropDownWithNotValidValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_not_value',
            'value'      => 'simple_drop_down_value_1'
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
    public function testElementIsNotValueOnSimpleDropDownWithValidValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_not_value',
            'value'      => 'simple_drop_down_value_0'
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
    public function testElementIsNotValueOnSimpleDropDownWithPlaceHolder(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_not_value',
            'value'      => 'simple_drop_down_value_1'
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
    public function testElementIsNotValueOnRadiosWithNoElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_not_value',
            'value'      => 'radio0'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsNotValueOnRadiosWithNotValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_not_value',
            'value'      => 'radio3'
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
    public function testElementIsNotValueOnRadiosWithValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_not_value',
            'value'      => 'radio3'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), 'radio1');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

}

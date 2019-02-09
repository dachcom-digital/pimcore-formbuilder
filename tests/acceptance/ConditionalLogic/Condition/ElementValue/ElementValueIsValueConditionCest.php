<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 * Condition "is_value". Must work on:
 *  - inputs
 * - textareas
 * - choice: default (dropDown) | extended (radio)
 */
class ElementValueIsValueConditionCest extends AbstractConditionCest
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
    public function testElementIsValueOnEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnSimpleInputWithValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'TEXT');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnSimpleInputWithNotValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'OTHER TEXT');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnEmptyTextArea(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnTextAreaWithValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), 'TEXT');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnTextAreaWithNotValidContent(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_area'],
            'comparator' => 'is_value',
            'value'      => 'TEXT'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), 'OTHER TEXT');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnSimpleDropDownWithFirstValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_value',
            'value'      => 'simple_drop_down_value_2'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnSimpleDropDownWithValidValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_value',
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
    public function testElementIsValueOnSimpleDropDownWithNotValidValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_value',
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
    public function testElementIsValueOnSimpleDropDownWithPlaceHolder(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_dropdown'],
            'comparator' => 'is_value',
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

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnRadiosWithNoElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_value',
            'value'      => 'radio0'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsValueOnRadiosWithValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_value',
            'value'      => 'radio3'
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
    public function testElementIsValueOnRadiosWithNotValidElementSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['radios'],
            'comparator' => 'is_value',
            'value'      => 'radio3'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), 'radio1');
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

}

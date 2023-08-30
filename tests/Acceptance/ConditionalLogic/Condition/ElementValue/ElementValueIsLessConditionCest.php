<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 * Condition "is_less". Must work on:
 * - input
 * - numeric
 * - choice: default (dropDown)
 */
class ElementValueIsLessConditionCest extends AbstractConditionCest
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
    public function testElementIsLessOnEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_less',
            'value'      => 10
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleInputWithGreaterValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_less',
            'value'      => 10
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 20);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleInputWithEqualValues(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_less',
            'value'      => 10
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 10);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleInputWithNegativeValues(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_less',
            'value'      => '-500'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), '-1000');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleInputWithLessValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_less',
            'value'      => 10
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 5);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleInputWithLessFloatValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_less',
            'value'      => 5.5
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 5.3);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnEmptySimpleNumericInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_numeric_input'],
            'comparator' => 'is_less',
            'value'      => 10
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldNumericInput('simple_numeric_input', [], [], ['not_blank']);
            return $testFormBuilder;
        });

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_numeric_input'), 5);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnEmptySimpleNumericInputWithNegativeValues(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_numeric_input'],
            'comparator' => 'is_less',
            'value'      => '-10'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldNumericInput('simple_numeric_input', [], [], ['not_blank']);
            return $testFormBuilder;
        });

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_numeric_input'), '-40');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleDropDownWithFirstValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['drop_down_with_numeric_values'],
            'comparator' => 'is_less',
            'value'      => 30
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder
                ->addFormFieldChoice('drop_down_with_numeric_values', [
                    '10' => '10',
                    '20' => '20',
                    '30' => '30',
                    '40' => '40',
                ]);
            return $testFormBuilder;
        });

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsLessOnSimpleDropDownWithGreaterValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['drop_down_with_numeric_values'],
            'comparator' => 'is_less',
            'value'      => 30
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldChoice('drop_down_with_numeric_values', [
                '10' => '10',
                '20' => '20',
                '30' => '30',
                '40' => '40',
            ]);
            return $testFormBuilder;
        });

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'drop_down_with_numeric_values'), '40');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }
}

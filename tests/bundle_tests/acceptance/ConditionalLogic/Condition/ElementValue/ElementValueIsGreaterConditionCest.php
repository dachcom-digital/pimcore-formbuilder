<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition\ElementValue;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractConditionCest;

/**
 * Condition "is_greater". Must work on:
 */
class ElementValueIsGreaterConditionCest extends AbstractConditionCest
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
    public function testElementIsGreaterOnEmptySimpleInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_greater',
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
    public function testElementIsGreaterOnSimpleInputWithGreaterValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_greater',
            'value'      => 50
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 100);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnSimpleInputWithEqualValues(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_greater',
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
    public function testElementIsGreaterOnSimpleInputWithNegativeValues(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_greater',
            'value'      => '-500'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), '-100');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnSimpleInputWithLessValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_greater',
            'value'      => 50
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 40);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnSimpleInputWithGreaterFloatValue(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_greater',
            'value'      => 100.5
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition]);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 100.8);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnEmptySimpleNumericInput(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_numeric_input'],
            'comparator' => 'is_greater',
            'value'      => 10
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldNumericInput('simple_numeric_input', [], [], ['not_blank']);
            return $testFormBuilder;
        });

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_numeric_input'), 50);
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnEmptySimpleNumericInputWithNegativeValues(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['simple_numeric_input'],
            'comparator' => 'is_greater',
            'value'      => '-50'
        ];

        $testFormBuilder = $this->runTestWithCondition($I, [$condition], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldNumericInput('simple_numeric_input', [], [], ['not_blank']);
            return $testFormBuilder;
        });

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_numeric_input'), '-10');
        // just to trigger javascript blur we need to fill another field (text2)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'text2');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnSimpleDropDownWithFirstValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['drop_down_with_numeric_values'],
            'comparator' => 'is_greater',
            'value'      => 20
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

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementIsGreaterOnSimpleDropDownWithGreaterValueSelected(AcceptanceTester $I)
    {
        $condition = [
            'type'       => 'elementValue',
            'fields'     => ['drop_down_with_numeric_values'],
            'comparator' => 'is_greater',
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

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1', 'div.fb-cl-hide-element'), 5);
    }
}

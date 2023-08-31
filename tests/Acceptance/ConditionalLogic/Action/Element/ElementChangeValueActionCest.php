<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\Support\AcceptanceTester;
use Facebook\WebDriver\WebDriverElement;

/**
 * Action "changeValue". Must work on:
 * - inputs
 * - textareas
 * - choice: default (dropDown) | multiple (multiSelect)
 */
class ElementChangeValueActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeValueOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'changeValue',
            'fields' => ['simple_text_input_2'],
            'value'  => 'changed_value'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), function (WebDriverElement $el) {
            return $el->getAttribute('value') === 'changed_value';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsChangeValueOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'changeValue',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'value'  => 'changed_value'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), function (WebDriverElement $el) {
            return $el->getAttribute('value') === 'changed_value';
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), function (WebDriverElement $el) {
            return $el->getAttribute('value') === 'changed_value';
        }, 5);

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeValueOnSimpleTextArea(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'changeValue',
            'fields' => ['simple_text_area'],
            'value'  => 'changed_value'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), function (WebDriverElement $el) {
            return $el->getAttribute('value') === 'changed_value';
        }, 5);

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeValueOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'changeValue',
            'fields' => ['simple_dropdown'],
            'value'  => 'simple_drop_down_value_1'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'), function (WebDriverElement $el) {
            return $el->getAttribute('value') === 'simple_drop_down_value_1';
        }, 5);

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeValueOnMultipleSelect(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'changeValue',
            'fields' => ['multiple_select'],
            'value'  => 'select_2'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), function (WebDriverElement $el) {
            return $el->getAttribute('value') === 'select_2';
        }, 5);
    }

}

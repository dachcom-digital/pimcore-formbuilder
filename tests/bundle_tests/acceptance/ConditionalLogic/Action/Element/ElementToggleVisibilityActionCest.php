<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;

/**
 * Action "toggleElement". Must work on:
 * - all fields
 */
class ElementToggleVisibilityActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementVisibilityShow(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2'],
            'state'  => 'show'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsVisibilityShow(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'state'  => 'show'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementVisibilityHide(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2'],
            'state'  => 'hide'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

       $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsVisibilityHide(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'state'  => 'hide'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', 'div.fb-cl-hide-element'), 5);
    }
}

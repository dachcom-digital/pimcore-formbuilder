<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;

/**
 * We only test not_blank validators since we're only testing javascript behaviour here.
 * Other constraint validation tests are functional tests.
 *
 * Action "constraintsAdd". Must work on:
 * - single checkbox
 * - inputs
 * - textareas
 * - choice: default (dropDown) | extended (radio) | multiple (multiSelect) | extended & multiple (checkboxes)
 */
class ElementAddValidationActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['simple_text_input_2'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsAddNotBlankValidationOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['simple_text_input_2', 'simple_text_input_3'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'));
        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnSimpleTextArea(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['simple_text_area'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_area', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_area', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnMultipleSelect(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['multiple_select'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'multiple_select', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'multiple_select', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['simple_dropdown'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnRadios(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['radios'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_1[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnCheckboxes(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['checkboxes'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2[required="required"]'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_3[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementAddNotBlankValidationOnSimpleCheckbox(AcceptanceTester $I)
    {
        $actions = [
            'type'       => 'constraintsAdd',
            'fields'     => ['single_checkbox'],
            'validation' => ['not_blank']
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->dontSeeElement($testFormBuilder->getFormFieldSelector(1, 'single_checkbox', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'single_checkbox', '', '[required="required"]'), 5);
    }

}

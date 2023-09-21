<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\Support\AcceptanceTester;

/**
 * We only test not_blank validators since we're only testing javascript behaviour here.
 * Other constraint validation tests are functional tests.
 *
 * Action "constraintsRemove". Must work on:
 * - single checkbox
 * - inputs
 * - textareas
 * - choice: default (dropDown) | extended (radio) | multiple (multiSelect) | extended & multiple (checkboxes)
 */
class ElementRemoveValidationActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['simple_text_input_2'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveAllValidationsOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['simple_text_input_2'],
            'validation'           => [],
            'removeAllValidations' => true
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsRemoveNotBlankValidationOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['simple_text_input_2', 'simple_text_input_3'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', '', '[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnSimpleTextArea(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['simple_text_area'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_area', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_area', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnMultipleSelect(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['multiple_select'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'multiple_select', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'multiple_select', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['simple_dropdown'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown', '', '[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnRadios(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['radios'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_1[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnCheckboxes(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['checkboxes'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2[required="required"]'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_3[required="required"]'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementRemoveNotBlankValidationOnSimpleCheckbox(AcceptanceTester $I)
    {
        $actions = [
            'type'                 => 'constraintsRemove',
            'fields'               => ['single_checkbox'],
            'validation'           => ['not_blank'],
            'removeAllValidations' => false
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], null, true);

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'single_checkbox', '', '[required="required"]'));

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'single_checkbox', '', '[required="required"]'), 5);
    }

}

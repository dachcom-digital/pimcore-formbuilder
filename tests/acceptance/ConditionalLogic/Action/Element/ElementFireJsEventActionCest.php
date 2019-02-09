<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;
use Facebook\WebDriver\WebDriverElement;

/**
 * Action "triggerEvent". Must work on:
 * - inputs
 * - textareas
 * - single checkbox
 * - choice: default (dropDown) | extended (radio) | multiple (multiSelect) | extended & multiple (checkboxes)
 */
class ElementFireJsEventActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['simple_text_input_2'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        // disable is not allowed initial
        $I->waitForElementNotVisible(
            $this->getJsSelector('simple_text_input_2', 'disable'),
            5
        );

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('simple_text_input_2', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('simple_text_input_2', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsFireJsEventOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('simple_text_input_2', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->waitForElementChange($this->getJsSelector('simple_text_input_3', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('simple_text_input_2', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);

        $I->waitForElementChange($this->getJsSelector('simple_text_input_3', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnSimpleTextArea(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['simple_text_area'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('simple_text_area', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('simple_text_area', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnMultipleSelect(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['multiple_select'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('multiple_select', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('multiple_select', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['simple_dropdown'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('simple_dropdown', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('simple_dropdown', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnRadios(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['radios'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('radios_0', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('radios_0', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnCheckboxes(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['checkboxes'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('checkboxes_0', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('checkboxes_0', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementFireJsEventOnSimpleCheckbox(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'triggerEvent',
            'fields' => ['single_checkbox'],
            'event'  => 'formbuilder.test.cl.action.fired_event'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($this->getJsSelector('single_checkbox', 'enable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.enable';
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($this->getJsSelector('single_checkbox', 'disable'), function (WebDriverElement $el) {
            return $el->getText() === 'event reached: formbuilder.test.cl.action.fired_event.disable';
        }, 5);
    }

    /**
     * @param string $fieldName
     * @param string $type
     *
     * @return string
     */
    private function getJsSelector($fieldName = 'simple_text_input_2', $type = 'enable')
    {
        return sprintf('.form-event-success[data-target="formbuilder_1_%s"][data-event="formbuilder.test.cl.action.fired_event.%s"]',
            $fieldName,
            $type
        );
    }

}

<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;
use Facebook\WebDriver\WebDriverElement;

/**
 * Action "toggleAvailability". Must work on:
 * - all fields
 */
class ElementToggleAvailabilityActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['simple_text_input_2'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsToggleAvailabilityOnSimpleInput(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnSimpleTextArea(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['simple_text_area'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnMultipleSelect(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['multiple_select'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'multiple_select'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnSimpleDropDownWithFirstElementSelected(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['simple_dropdown'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnRadios(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['radios'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_1'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        // invalidate condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_1'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnCheckboxes(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['checkboxes'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_3'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        // invalidate condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_3'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementToggleAvailabilityOnSimpleCheckbox(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleAvailability',
            'fields' => ['single_checkbox'],
            'state'  => 'disable'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'single_checkbox'), function (WebDriverElement $el) {
            return !$el->isEnabled();
        }, 5);

        // invalidate condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'invalid_text1');

        $I->waitForElementChange($testFormBuilder->getFormFieldSelector(1, 'single_checkbox'), function (WebDriverElement $el) {
            return $el->isEnabled();
        }, 5);
    }
}

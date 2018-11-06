<?php

namespace DachcomBundle\Test\acceptance\ExtJs;

use DachcomBundle\Test\AcceptanceTester;

class ConditionalLogicCest extends AbstractExtjs
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsConditionalLogicBlockAddition(AcceptanceTester $I)
    {
        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);

        // add a field
        $this->addFieldToForm($I, $formId, 'text_field');

        // add a field
        $this->addFieldToForm($I, $formId, 'another_text_field');

        // add conditional section
        $sectionId = $this->addConditionalLogicBlockToForm($I, $formId);

        // CONDITION
        $conditionId = $this->addConditionToSection($I, $formId, $sectionId['selector'], 'Element Value');

        // field
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.condition.%d.fields"]', $sectionId['index'], $conditionId['index']), 60, 10);
        $I->waitForText('text_field', 10, 'ul.x-list-plain[aria-hidden="false"]');
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="text_field"]');

        // comparator
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.condition.%d.comparator"]', $sectionId['index'], $conditionId['index']), 60, 10);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Contains"]');

        // value
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.condition.%d.value"]', $sectionId['index'], $conditionId['index']));
        $I->fillField(sprintf('input[name="cl.%d.condition.%d.value"]', $sectionId['index'], $conditionId['index']), 'test');

        // ACTION
        $actionId = $this->addActionToSection($I, $formId, $sectionId['selector'], 'Toggle Visibility');

        // field
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.action.%d.fields"]', $sectionId['index'], $actionId['index']), 100, 10);
        $I->waitForText('another_text_field', 10, 'ul.x-list-plain[aria-hidden="false"]');
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="another_text_field"]');

        // state
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.action.%d.state"]', $sectionId['index'], $actionId['index']), 100, 10);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Hide"]');

        //Form successfully saved
        $I->click('Save');
        $I->waitForText('Form successfully saved', 10);
    }
}

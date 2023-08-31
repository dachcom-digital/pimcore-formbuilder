<?php

namespace DachcomBundle\Test\Acceptance\ExtJs;

use DachcomBundle\Test\Support\AcceptanceTester;

class ConditionalLogicCest extends AbstractExtJs
{
    public function testExtJsConditionalLogicBlockAddition(AcceptanceTester $I): void
    {
        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);

        $this->addFieldToForm($I, $formId, 'text_field');
        $this->addFieldToForm($I, $formId, 'another_text_field');

        // add conditional section
        $sectionId = $this->addConditionalLogicBlockToForm($I, $formId);

        // CONDITION
        $conditionId = $this->addConditionToSection($I, $formId, $sectionId['selector'], ['text_field']);

        // ACTION
        $actionId = $this->addActionToSection($I, $formId, $sectionId['selector'], ['another_text_field']);

        //Form successfully saved
        $I->click('Save', $this->getFormPanelSelector($formId));
        $I->waitForText('Form successfully saved', 10);
    }
}

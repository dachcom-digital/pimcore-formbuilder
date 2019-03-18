<?php

namespace DachcomBundle\Test\acceptance\ExtJs;

use DachcomBundle\Test\AcceptanceTester;

class FieldAttributesCest extends AbstractExtJs
{
    /**
     * @param AcceptanceTester $I
     *
     * @return string
     * @throws \Exception
     */
    protected function setupDefaultField(AcceptanceTester $I)
    {
        $this->amOnFormBuilderBackendBuilder($I);
        $formId = $this->seeExtJsForm($I);

        return $formId;

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testAttributesOnFieldsetContainer(AcceptanceTester $I)
    {
        $formId = $this->setupDefaultField($I);

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));

        $I->click('Add container');
        $I->waitForText('Fieldset', 10);
        $I->click('Fieldset');

        $fieldSelector = function ($sel) {
            return sprintf('%s %s', '.form-builder-form-configuration-panel', $sel);
        };

        // add default attribute group
        $I->waitForText('Attributes', 10, $fieldSelector('legend'));
        $I->clickWithLeftButton(['css' => $fieldSelector('fieldset[aria-label="Attributes field set"] .x-form-trigger')]);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Default"]');

        $I->click('Add Field', '.form-builder-form-configuration-panel');
        $I->waitForText('Option:', 10, $fieldSelector('fieldset[aria-label="Attributes field set"] .x-container-default'));
        $I->waitForText('Value:', 10, $fieldSelector('fieldset[aria-label="Attributes field set"] .x-container-default'));

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testAttributesOnRepeaterContainer(AcceptanceTester $I)
    {
        $formId = $this->setupDefaultField($I);

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));

        $I->click('Add container');
        $I->waitForText('Fieldset', 10);
        $I->click('Repeater');

        $fieldSelector = function ($sel) {
            return sprintf('%s %s', '.form-builder-form-configuration-panel', $sel);
        };

        // add default attribute group
        $I->waitForText('Attributes', 10, $fieldSelector('legend'));
        $I->clickWithLeftButton(['css' => $fieldSelector('fieldset[aria-label="Attributes field set"] .x-form-trigger')]);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Default"]');

        $I->click('Add Field', '.form-builder-form-configuration-panel');
        $I->waitForText('Option:', 10, $fieldSelector('fieldset[aria-label="Attributes field set"] .x-container-default'));
        $I->waitForText('Value:', 10, $fieldSelector('fieldset[aria-label="Attributes field set"] .x-container-default'));

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testAttributesOnDefaultField(AcceptanceTester $I)
    {
        $formId = $this->setupDefaultField($I);

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));

        $I->click('Add form element');
        $I->waitForText('Text Fields', 10);

        $I->click('Text Fields');
        $I->waitForText('Text Type', 10);
        $I->click('Text Type');

        $fieldSelector = function ($sel) {
            return sprintf('%s %s', '.form-builder-form-configuration-panel', $sel);
        };

        // add default attribute group
        $I->clickWithLeftButton(['css' => $fieldSelector('fieldset[aria-label="Attributes field set"] .x-form-trigger')]);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Default"]');

        $I->click('Add Field', '.form-builder-form-configuration-panel');
        $I->waitForText('Option:', 10, $fieldSelector('fieldset[aria-label="Attributes field set"] .x-container-default'));
        $I->waitForText('Value:', 10, $fieldSelector('fieldset[aria-label="Attributes field set"] .x-container-default'));

    }
}

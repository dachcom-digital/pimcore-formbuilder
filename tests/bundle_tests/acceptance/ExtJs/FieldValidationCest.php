<?php

namespace DachcomBundle\Test\acceptance\ExtJs;

use DachcomBundle\Test\AcceptanceTester;

class FieldValidationCest extends AbstractExtjs
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFieldWithInvalidFormData(AcceptanceTester $I)
    {
        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));

        $I->waitForText('Add form element', 10);

        $I->click('Add form element');
        $I->waitForText('Text Fields', 10);

        $I->click('Text Fields');
        $I->waitForText('Text Type', 10);

        $I->click('Text Type');
        $I->waitForElementVisible('input[name="display_name"]', 10);

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));
        $I->waitForText('Some form elements are invalid', 10);
    }
}

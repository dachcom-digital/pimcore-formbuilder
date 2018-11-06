<?php

namespace DachcomBundle\Test\acceptance\ExtJs;

use DachcomBundle\Test\AcceptanceTester;

class ContextMenuCest extends AbstractExtjs
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuOnRootNode(AcceptanceTester $I)
    {
        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);
        $I->clickWithRightButton($this->getRootNodeOfForm($formId));

        $I->wait(0.2);
        $I->dontSee('Delete');
        $I->dontSee('Copy');
        $I->dontSee('Add validation');
        $I->see('Add form element');

        $I->click('Add form element');
        $I->waitForText('Text Fields', 10);

        $I->click('Text Fields');
        $I->waitForText('Text Type', 10);

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuOnFieldNode(AcceptanceTester $I)
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
        $I->fillField('input[name="display_name"]', 'field_name_one');

        $I->clickWithRightButton('.form_builder_icon_text');

        $I->wait(0.2);
        $I->dontSee('Add form element');
        $I->see('Delete');
        $I->see('Copy');
        $I->see('Add validation');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuOnConstraintFieldNode(AcceptanceTester $I)
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
        $I->fillField('input[name="display_name"]', 'field_name_one');

        $I->clickWithRightButton('.form_builder_icon_text');

        $I->wait(0.2);
        $I->click('Add validation');

        $I->waitForText('Not Blank', 10);
        $I->click('Not Blank');

        $I->clickWithRightButton('.form_builder_icon_validation');
        $I->wait(0.2);
        $I->dontSee('Add form element');
        $I->dontSee('Add validation');
        $I->see('Delete');
        $I->see('Copy');

    }
}

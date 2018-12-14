<?php

namespace DachcomBundle\Test\acceptance\ExtJs;

use DachcomBundle\Test\AcceptanceTester;

class ContextMenuCest extends AbstractExtJs
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
        $I->see('Add container');

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
        $I->dontSee('Add container');
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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuOnContainerFieldNode(AcceptanceTester $I)
    {
        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);
        $I->clickWithRightButton($this->getRootNodeOfForm($formId));
        $I->waitForText('Add container', 10);
        $I->click('Add container');
        $I->waitForText('Fieldset', 10);
        $I->click('Fieldset');
        $I->waitForElementVisible('input[name="name"]', 10);
        $I->fillField('input[name="name"]', 'fieldset_one');

        $I->clickWithRightButton('.form_builder_icon_container_type');
        $I->wait(0.2);
        $I->see('Add form element');
        $I->dontSee('Add validation');
        $I->see('Delete');
        $I->see('Copy');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuCopyPasteField(AcceptanceTester $I)
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

        $I->clickWithRightButton('//span[text()="field_name_one"]');
        $I->waitForText('Copy', 10);
        $I->click('Copy');

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));
        $I->waitForText('Paste', 10);
        $I->click('Paste');

        $I->see('field_name_one field_name_one', $this->getFormElementsTreeSelector($formId));

        $I->clickWithRightButton(
            sprintf('%s table:last-child', $this->getFormElementsTreeSelector($formId))
        );

        $I->waitForText('Copy', 10);
        $I->waitForText('Add validation', 10);
        $I->waitForText('Delete', 10);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuDeleteField(AcceptanceTester $I)
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

        $I->clickWithRightButton('//span[text()="field_name_one"]');
        $I->waitForText('Delete', 10);
        $I->click('Delete');

        $I->waitForElementNotVisible('//span[text()="field_name_one"]', 10);

    }


    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testExtJsFormContextMenuDeleteContainerField(AcceptanceTester $I)
    {
        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);

        $I->clickWithRightButton($this->getRootNodeOfForm($formId));
        $I->waitForText('Add container', 10);
        $I->click('Add container');
        $I->waitForText('Fieldset', 10);
        $I->click('Fieldset');
        $I->waitForElementVisible('input[name="name"]', 10);
        $I->fillField('input[name="name"]', 'fieldset_one');

        $I->clickWithRightButton('.form_builder_icon_container_type');
        $I->waitForText('Add form element', 10);
        $I->click('Add form element');
        $I->waitForText('Text Fields', 10);
        $I->click('Text Fields');
        $I->waitForText('Text Type', 10);
        $I->click('Text Type');
        $I->waitForElementVisible('input[name="display_name"]', 10);
        $I->fillField('input[name="display_name"]', 'field_name_one');

        $I->clickWithRightButton('.form_builder_icon_container_type');
        $I->waitForText('Delete', 10);
        $I->click('Delete');

        $I->waitForElementNotVisible('.form_builder_icon_container_type', 10);
        $I->waitForElementNotVisible('//span[text()="field_name_one"]', 10);
    }
}

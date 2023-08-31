<?php

namespace DachcomBundle\Test\Acceptance\ExtJs;

use DachcomBundle\Test\Support\AcceptanceTester;

abstract class AbstractExtJs
{
    /**
     * @var array
     */
    private $formIds = [];

    /**
     * @var int
     */
    private $formIdIncrementor = 1;

    /**
     * @var string|null
     */
    private $env = null;

    /**
     * @param AcceptanceTester      $I
     * @param \Codeception\Scenario $scenario
     */
    public function _before(AcceptanceTester $I, \Codeception\Scenario $scenario)
    {
        $this->formIdIncrementor = 1;
        $this->env = $scenario->current('env');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    protected function amOnFormBuilderBackendBuilder(AcceptanceTester $I)
    {
        $I->haveAUserWithAdminRights('backendTester');

        $I->amOnPage('/admin');
        $I->submitForm('form', ['username' => 'backendTester', 'password' => 'backendTester']);

        // wait for pimcore gui
        $I->waitForElement('div#pimcore_panel_tree_objects', 10);

        // close left pimcore tree
        $I->click('.pimcore_main_accordion + .x-splitter > div');

        // wait for pimcore settings => click
        $I->waitForElement('li#pimcore_menu_settings', 10);
        $I->click('li#pimcore_menu_settings');

        // wait for formbuilder icon => click
        $I->waitForElement('div#form_builder_setting_button', 10);
        $I->click('#form_builder_setting_button');

        $I->waitForElement('#form_builder_settings', 10);
    }

    /**
     * @param AcceptanceTester $I
     * @param string           $formName
     *
     * @return mixed
     * @throws \Exception
     */
    protected function seeExtJsForm(AcceptanceTester $I, $formName = 'TEST FORM EXTJS')
    {
        // form exists: just activate it
        if (isset($this->forms[$formName])) {
            $I->click(sprintf('%s table tr[data-qtip="ID: %d"]', $this->getFormsTreeSelectorBody(), $this->formIds[$formName]));
            $I->waitForElement($this->getFormPanelSelector($this->formIds[$formName]), 10);
            return $this->formIds[$formName];
        }

        $formId = $this->formIdIncrementor;

        $I->click('Add Form', $this->getFormsTreeSelectorToolBar());
        $I->waitForElement($this->getNewFormMessageBoxSelector(), 10);
        $I->fillField(sprintf('%s input', $this->getNewFormMessageBoxSelector()), $formName);

        $I->click('OK', $this->getNewFormMessageBoxSelector());

        // check if new form has been added to main tree
        $I->waitForElement(sprintf('%s table tr[data-qtip="ID: %d"]', $this->getFormsTreeSelectorBody(), $formId), 10);

        // get active tab
        $I->waitForElement($this->getFormPanelSelector($formId), 10);

        $this->formIds[$formName] = $formId;
        $this->formIdIncrementor++;

        return $formId;
    }

    /**
     * @param AcceptanceTester $I
     * @param                  $formId
     * @param string           $displayName
     *
     * @throws \Exception
     */
    protected function addFieldToForm(AcceptanceTester $I, $formId, $displayName = 'text_field')
    {
        $I->clickWithRightButton($this->getRootNodeOfForm($formId));

        $I->waitForText('Add form element', 10);

        $I->click('Add form element');
        $I->waitForText('Text Fields', 10);

        $I->click('Text Fields');
        $I->waitForText('Text Type', 10);

        $I->click('Text Type');
        $I->waitForElementVisible(sprintf('%s input[name="display_name"]', $this->getFormPanelSelector($formId)), 10);

        $I->fillField(sprintf('%s input[name="display_name"]', $this->getFormPanelSelector($formId)), $displayName);

    }

    /**
     * @param AcceptanceTester $I
     * @param                  $formId
     *
     * @return array
     * @throws \Exception
     */
    protected function addConditionalLogicBlockToForm(AcceptanceTester $I, $formId)
    {
        $blockIndex = 0;

        $I->click($this->getRootNodeOfForm($formId));
        $I->click('Add', $this->getConditionalFieldSetSelector($formId));

        $conditionalBlockSelector = sprintf('%s fieldset:nth-of-type(%d)', $this->getConditionalFieldSetSelector($formId), $blockIndex + 1);

        $I->waitForElement($conditionalBlockSelector, 10);
        $id = $I->grabAttributeFrom($conditionalBlockSelector, 'id');

        return ['selector' => $id, 'index' => $blockIndex];
    }

    /**
     * @param AcceptanceTester $I
     * @param                  $formId
     * @param                  $sectionId
     * @param array            $fieldsToSelect
     *
     * @return array
     * @throws \Exception
     */
    protected function addConditionToSection(AcceptanceTester $I, $formId, $sectionId, array $fieldsToSelect)
    {
        $conditionBlockIndex = 0;

        // click "conditions" tab
        $I->click('Conditions', sprintf('#%s', $sectionId));
        $I->wait(0.2);

        $activePanelToolbarSelector = sprintf('#%s .x-panel:not(.x-hidden-offsets) > div > .x-toolbar-default', $sectionId);
        $activePanelPanelSelector = sprintf('%s + .x-panel-body', $activePanelToolbarSelector);

        $I->click(sprintf('%s .pimcore_icon_add', $activePanelToolbarSelector));
        $I->click('Element Value');

        $conditionBlockSelector = sprintf('%s .x-panel:nth-of-type(%d)', $activePanelPanelSelector, $conditionBlockIndex + 1);

        $I->waitForElement($conditionBlockSelector, 10);
        $id = $I->grabAttributeFrom($conditionBlockSelector, 'id');

        // scroll to field
        $this->scrollConfigurationPanel($I, $formId);

        // POPULATE CONDITION

        // field
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.condition.%d.fields"]', $id, $conditionBlockIndex), 80, 10);
        $I->waitForText($fieldsToSelect[0], 10, 'ul.x-list-plain[aria-hidden="false"]');
        $I->clickWithLeftButton(sprintf('//ul[@aria-hidden="false"]//li[text()="%s"]', $fieldsToSelect[0]));

        // comparator
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.condition.%d.comparator"]', $id, $conditionBlockIndex), 80, 10);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Contains"]');

        // value
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.condition.%d.value"]', $id, $conditionBlockIndex));
        $I->fillField(sprintf('input[name="cl.%d.condition.%d.value"]', $id, $conditionBlockIndex), 'test');

        return ['selector' => $id, 'index' => $conditionBlockIndex];
    }

    /**
     * @param AcceptanceTester $I
     * @param                  $formId
     * @param                  $sectionId
     * @param array            $fieldsToSelect
     *
     * @return array
     * @throws \Exception
     */
    protected function addActionToSection(AcceptanceTester $I, $formId, $sectionId, array $fieldsToSelect)
    {
        $actionBlockIndex = 0;

        // click "actions" tab
        $I->click('Actions');
        $I->wait(0.2);

        $activePanelToolbarSelector = sprintf('#%s .x-panel:not(.x-hidden-offsets) > div > .x-toolbar-default', $sectionId);
        $activePanelPanelSelector = sprintf('%s + .x-panel-body', $activePanelToolbarSelector);

        $I->click(sprintf('%s .pimcore_icon_add', $activePanelToolbarSelector));
        $I->click('Toggle Visibility');

        $ActionBlockSelector = sprintf('%s .x-panel:nth-of-type(%d)', $activePanelPanelSelector, $actionBlockIndex + 1);

        $I->waitForElement($ActionBlockSelector, 10);
        $id = $I->grabAttributeFrom($ActionBlockSelector, 'id');

        // scroll to field
        $this->scrollConfigurationPanel($I, $formId);

        // POPULATE ACTION

        // field
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.action.%d.fields"]', $id, $actionBlockIndex), 120, 10);
        $I->waitForText($fieldsToSelect[0], 10, 'ul.x-list-plain[aria-hidden="false"]');
        $I->clickWithLeftButton(sprintf('//ul[@aria-hidden="false"]//li[text()="%s"]', $fieldsToSelect[0]));

        // state
        $I->clickWithLeftButton(sprintf('input[name="cl.%d.action.%d.state"]', $id, $actionBlockIndex), 120, 10);
        $I->clickWithLeftButton('//ul[@aria-hidden="false"]//li[text()="Hide"]');

        return ['selector' => $id, 'index' => $actionBlockIndex];

    }

    /**
     * @param AcceptanceTester $I
     * @param                  $formId
     * @param int              $scrollTop
     */
    protected function scrollConfigurationPanel(AcceptanceTester $I, $formId, $scrollTop = 500)
    {
        // scroll to field
        $id = $I->grabAttributeFrom(sprintf('%s > div > .x-panel-body', $this->getFormConfigurationPanelSelector($formId)), 'id');
        $I->executeJS(sprintf('document.getElementById("%s").scrollTop = %d', $id, $scrollTop));
    }

    /**
     * @param $formId
     *
     * @return string
     */
    protected function getConditionalFieldSetSelector($formId)
    {
        return sprintf('%s .form-builder-conditional-logic-field-set', $this->getFormConfigurationPanelSelector($formId));
    }

    /**
     * @return string
     */
    protected function getFormsTreeSelectorToolBar()
    {
        return '.form-builder-form-selector-tree .x-toolbar';
    }

    /**
     * @return string
     */
    protected function getFormsTreeSelectorBody()
    {
        return '.form-builder-form-selector-tree .x-panel-body';
    }

    /**
     * @param $formId
     *
     * @return string
     */
    protected function getRootNodeOfForm($formId)
    {
        return sprintf('%s .form_builder_icon_root',
            $this->getFormElementsTreeSelector($formId)
        );
    }

    /**
     * @param $formId
     *
     * @return string
     */
    protected function getFormElementsTreeSelector($formId)
    {
        return sprintf('%s .form-builder-form-elements-tree',
            $this->getFormPanelSelector($formId)
        );
    }

    /**
     * @param $formId
     *
     * @return string
     */
    protected function getFormConfigurationPanelSelector($formId)
    {
        return sprintf('%s .form-builder-form-configuration-panel',
            $this->getFormPanelSelector($formId)
        );
    }

    /**
     * @param $formId
     *
     * @return string
     */
    protected function getFormPanelSelector($formId)
    {
        return sprintf('.form-builder-form-panel[data-form-id="%s"]:not(.x-hidden-offsets)', $formId);
    }

    /**
     * @return string
     */
    protected function getNewFormMessageBoxSelector()
    {
        return '.x-message-box[aria-hidden="false"]';
    }

    /**
     * @return string
     */
    protected function getUploadBoxFileInputSelector()
    {
        return '.x-window[aria-hidden="false"] input.x-form-file-input';
    }
}

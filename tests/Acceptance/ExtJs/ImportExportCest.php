<?php


namespace DachcomBundle\Test\Acceptance\ExtJs;

use DachcomBundle\Test\Support\AcceptanceTester;

class ImportExportCest extends AbstractExtJs
{
    public function testFormExportImport(AcceptanceTester $I): void
    {
        $I->setDownloadPathForWebDriver();

        $this->amOnFormBuilderBackendBuilder($I);

        $formId = $this->seeExtJsForm($I);

        $this->populateFormForExport($I, $formId);

        $I->click('Save', $this->getFormPanelSelector($formId));
        $I->waitForText('Form successfully saved', 10);

        $formExportName = 'form_export_1.yaml';

        $I->click('Export', $this->getFormPanelSelector($formId));
        $I->wait(3);

        $I->seeDownload($formExportName);

        // create new form and import exported data
        $secondFormId = $this->seeExtJsForm($I, 'NEW_FORM');

        $I->click('Import', $this->getFormPanelSelector($secondFormId));
        $I->wait(1);

        $I->see('Yes');
        $I->click('Yes');
        $I->wait(1);

        $I->attachFile($this->getUploadBoxFileInputSelector(), sprintf('downloads/%s', $formExportName));
        $I->wait(5);

        $I->see('first_text_field', $this->getFormElementsTreeSelector($secondFormId));
        $I->see('second_text_field', $this->getFormElementsTreeSelector($secondFormId));
        $I->see('third_text_field', $this->getFormElementsTreeSelector($secondFormId));

        // scroll to field
        $this->scrollConfigurationPanel($I, $secondFormId);

        $I->click('Conditions', $this->getConditionalFieldSetSelector($secondFormId));
        $I->see('first_text_field', $this->getConditionalFieldSetSelector($secondFormId));
        $I->seeElement(sprintf('%s input[value="Contains"]', $this->getConditionalFieldSetSelector($secondFormId)));
        $I->seeElement(sprintf('%s input[value="test"]', $this->getConditionalFieldSetSelector($secondFormId)));

        $I->click('Actions', $this->getConditionalFieldSetSelector($secondFormId));
        $I->see('second_text_field', $this->getConditionalFieldSetSelector($secondFormId));
        $I->seeElement(sprintf('%s input[value="Hide"]', $this->getConditionalFieldSetSelector($secondFormId)));
    }

    protected function populateFormForExport(AcceptanceTester $I, $formId): void
    {
        $this->addFieldToForm($I, $formId, 'first_text_field');
        $this->addFieldToForm($I, $formId, 'second_text_field');
        $this->addFieldToForm($I, $formId, 'third_text_field');

        // add conditional section
        $sectionId = $this->addConditionalLogicBlockToForm($I, $formId);

        // CONDITION
        $conditionId = $this->addConditionToSection($I, $formId, $sectionId['selector'], ['first_text_field']);

        // ACTION
        $actionId = $this->addActionToSection($I, $formId, $sectionId['selector'], ['second_text_field']);
    }
}

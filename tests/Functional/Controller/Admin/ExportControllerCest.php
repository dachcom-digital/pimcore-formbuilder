<?php

namespace DachcomBundle\Test\Functional\Controller\Admin;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\Support\Helper\Traits;

class ExportControllerCest
{
    use Traits\FunctionalFormTrait;

    public function testExportFormEmailsActionEmptyAction(FunctionalTester $I)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage('/admin/formbuilder/export/mail-csv-export/' . $form->getId());

        $I->see('NO_CSV_DATA_FOUND');
    }

    public function testExportFormEmailsActionActionWithAllTypes(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $userEmail = $I->haveAEmailDocumentForUser();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, $userEmail);
        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage(sprintf('/admin/formbuilder/export/mail-csv-export/%d', $form->getId()));

        $header = [
            'form_id',
            'log_id',
            'email_path',
            'email_id',
            'preset',
            'output_workflow_name',
            'to',
            'cc',
            'bcc',
            'sent_date',
            'subject',
        ];

        $I->seeResponseIsCsv();
        $I->seeResponseCsvHeaderHasValues($header);
        $I->seeResponseCsvLength(3);
        $I->seeResponseCsvRowValues(1, ['form_id' => $form->getId(), 'output_workflow_name' => 'Test Output Workflow', 'email_path' => $adminEmail->getFullPath()]);
        $I->seeResponseCsvRowValues(2, ['form_id' => $form->getId(), 'output_workflow_name' => 'Test Output Workflow', 'email_path' => $userEmail->getFullPath()]);
    }

    public function testExportFormEmailsActionActionWithOutputWorkflowFilter(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();
        $document = $I->haveAPageDocument('form-test');
        $firstMail = $I->haveAEmailDocumentForAdmin();
        $secondMail = $I->haveAEmailDocumentForUser();

        $firstOutputWorkflowChannels = [
            [
                'type' => 'email',
                'email' => $firstMail
            ]
        ];

        $secondOutputWorkflowChannels = [
            [
                'type' => 'email',
                'email' => $secondMail
            ]
        ];

        $form = $I->haveAForm($testFormBuilder);
        $firstOutputWorkflow = $I->haveAOutputWorkflow('First Test Output Workflow', $form, $firstOutputWorkflowChannels);
        $secondOutputWorkflow = $I->haveAOutputWorkflow('Second Test Output Workflow', $form, $secondOutputWorkflowChannels);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $firstMail, $secondMail, 'form_div_layout.html.twig', $firstOutputWorkflow);
        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');

        $I->amOnPage(sprintf('/admin/formbuilder/export/mail-csv-export/%s?mailType=%s', $form->getId(), $firstOutputWorkflow->getId()));
        $I->seeResponseIsCsv();
        $I->seeResponseCsvLength(2);
        $I->seeResponseCsvRowValues(1, ['form_id' => $form->getId(), 'output_workflow_name' => 'First Test Output Workflow', 'email_path' => $firstMail->getFullPath()]);

        $I->amOnPage(sprintf('/admin/formbuilder/export/mail-csv-export/%s?mailType=%s', $form->getId(), $secondOutputWorkflow->getId()));
        $I->see('NO_CSV_DATA_FOUND');
    }
}

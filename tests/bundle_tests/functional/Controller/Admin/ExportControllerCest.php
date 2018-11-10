<?php

namespace DachcomBundle\Test\functional\Controller\Admin;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use DachcomBundle\Test\Helper\Traits;

class ExportControllerCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     */
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

    /**
     * @param FunctionalTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     */
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
        $I->amOnPage('/admin/formbuilder/export/mail-csv-export/' . $form->getId());

        $header = [
            'form_id',
            'log_id',
            'email_path',
            'email_id',
            'preset',
            'is_copy',
            'to',
            'cc',
            'bcc',
            'sent_date',
            'subject',
        ];

        $I->seeResponseIsCsv();
        $I->seeResponseCsvHeaderHasValues($header);
        $I->seeResponseCsvLength(3);
        $I->seeResponseCsvRowValues(1, ['form_id' => $form->getId(), 'is_copy' => '0', 'email_path' => $adminEmail->getFullPath()]);
        $I->seeResponseCsvRowValues(2, ['form_id' => $form->getId(), 'is_copy' => '1', 'email_path' => $userEmail->getFullPath()]);
    }

    /**
     * @depends testExportFormEmailsActionActionWithAllTypes
     *
     * @param FunctionalTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function testExportFormEmailsActionActionWithMainType(FunctionalTester $I)
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
        $I->amOnPage(sprintf('/admin/formbuilder/export/mail-csv-export/%s?mailType=%s', $form->getId(), 'only_main'));

        $I->seeResponseIsCsv();
        $I->seeResponseCsvLength(2);
        $I->seeResponseCsvRowValues(1, ['form_id' => $form->getId(), 'is_copy' => '0', 'email_path' => $adminEmail->getFullPath()]);
    }

    /**
     * @depends testExportFormEmailsActionActionWithAllTypes
     *
     * @param FunctionalTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function testExportFormEmailsActionActionWithCopyType(FunctionalTester $I)
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
        $I->amOnPage(sprintf('/admin/formbuilder/export/mail-csv-export/%s?mailType=%s', $form->getId(), 'only_copy'));

        $I->seeResponseIsCsv();
        $I->seeResponseCsvLength(2);
        $I->seeResponseCsvRowValues(1, ['form_id' => $form->getId(), 'is_copy' => '1', 'email_path' => $userEmail->getFullPath()]);
    }
}

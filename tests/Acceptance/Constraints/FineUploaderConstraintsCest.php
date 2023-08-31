<?php

namespace DachcomBundle\Test\Acceptance\Constraints;

use DachcomBundle\Test\Support\AcceptanceTester;
use DachcomBundle\Test\Support\Util\TestFormBuilder;

class FineUploaderConstraintsCest
{
    public function testEmptyFileUploadFormWithNotBlankConstraint(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yaml');

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForElementNotVisible('.form-success-wrapper', 15);
    }

    public function testPopulatedFileUploadFormWithNotBlankConstraint(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yaml');

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);
        $I->waitForElement('input[type="file"]', 5);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));
        $I->waitForElement('.qq-file-id-0.qq-upload-success', 15);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForElementVisible('.form-success-wrapper', 15);
    }
}

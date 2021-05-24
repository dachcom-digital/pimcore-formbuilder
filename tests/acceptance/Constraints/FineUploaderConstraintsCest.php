<?php

namespace DachcomBundle\Test\acceptance\Constraints;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class FineUploaderConstraintsCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testEmptyFileUploadFormWithNotBlankConstraint(AcceptanceTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_default.yml');

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForElementNotVisible('.form-success-wrapper', 15);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testPopulatedFileUploadFormWithNotBlankConstraint(AcceptanceTester $I)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

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

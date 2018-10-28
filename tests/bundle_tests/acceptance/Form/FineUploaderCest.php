<?php

namespace DachcomBundle\Test\acceptance\Form;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\AcceptanceTester;

class FineUploaderCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithOneFile(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithOneLargeFile(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveFile($fileName, 25);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 80);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 40, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithMultipleFile(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $fileName = 'test.pdf';
        $I->haveFile($fileName, 2);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-1.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedAllowedExtensionsMustPass(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $allowedExtensions = ['pdf', 'txt'];
        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'allowed_extensions' => $allowedExtensions,
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedAllowedExtensionsMustFail(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $allowedExtensions = ['pdf', 'txt'];
        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'allowed_extensions' => $allowedExtensions
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.png';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForText(
            sprintf('%s has an invalid extension. Valid extension(s): %s.', $fileName, implode(', ', $allowedExtensions)),
            5,
            '.qq-dialog-message-selector'
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedItemsLimitMustPass(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'item_limit' => 1
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedItemsLimitMustFail(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'item_limit' => 1
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $I->haveFile('test.pdf', 2);
        $I->attachFile('input[type="file"]', 'generated/test.pdf');

        $I->waitForText(
            'Too many items (2) would be uploaded. Item limit is 1.',
            5,
            '.qq-dialog-message-selector'
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedFileSizeMustPass(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'max_file_size' => 5
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveFile($fileName, 3);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedFileSizeMustFail(AcceptanceTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'max_file_size' => 5
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveFile($fileName, 7);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForText(
            sprintf('%s is too large', $fileName),
            5,
            '.qq-dialog-message-selector'
        );
    }
}

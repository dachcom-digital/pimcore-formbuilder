<?php

namespace DachcomBundle\Test\Acceptance\Form;

use DachcomBundle\Test\Support\Helper\Traits;
use DachcomBundle\Test\AcceptanceTester;

class FineUploaderCest
{
    use Traits\FunctionalFormTrait;

    public function testUploadFormWithOneFile(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    public function testUploadFormWithOneLargeFile(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 25);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 80);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 40, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    public function testUploadFormWithMultipleFile(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 2);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-1.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    public function testUploadFormWithLimitedAllowedExtensionsMustPass(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

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

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    public function testUploadFormWithLimitedAllowedExtensionsMustFail(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

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

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.png';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForText(
            'Invalid file extension.',
            5,
            '.qq-dialog-message-selector'
        );
    }

    public function testUploadFormWithLimitedItemsLimitMustPass(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

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

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    public function testUploadFormWithLimitedItemsLimitMustFail(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

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

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $I->haveADummyFile('test.pdf', 2);
        $I->attachFile('input[type="file"]', 'generated/test.pdf');

        $I->waitForText(
            'Too many items would be uploaded.',
            5,
            '.qq-dialog-message-selector'
        );
    }

    public function testUploadFormWithLimitedFileSizeMustPass(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

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

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 3);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->seePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->seeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }

    public function testUploadFormWithLimitedFileSizeMustFail(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

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

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 7);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForText(
            'File is too large',
            5,
            '.qq-dialog-message-selector'
        );
    }

    public function testUploadFormWithFilesAsAttachment(AcceptanceTester $I): void
    {
        $I->haveABootedSymfonyConfiguration('config_fineuploaderjs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField(
            'dynamic_multi_file',
            'file_upload',
            'File Upload',
            [],
            [
                'submit_as_attachment' => true,
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('fine-uploader', ['action' => 'dynamicMultiFileAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->amOnPage('/fine-uploader');

        $I->waitForElement('div.qq-upload-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.qq-file-id-0.qq-upload-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->cantSeePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->cantSeeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }
}

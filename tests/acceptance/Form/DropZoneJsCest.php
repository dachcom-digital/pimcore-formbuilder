<?php

namespace DachcomBundle\Test\acceptance\Form;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\AcceptanceTester;

class DropZoneJsCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithOneFile(AcceptanceTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'Drop Zone Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);

        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 5);

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 25);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 80);

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $testFormBuilder->addFormField('dynamic_multi_file', 'file_upload', 'File Upload');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));
        $I->waitForText('test.txt', 5, '.dz-success');

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 2);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));
        $I->waitForText('test.pdf', 5, '.dz-success:nth-of-type(3)');

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $allowedExtensions = ['application/pdf', 'text/plain'];
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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 5);

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

        $testFormBuilder = $this->generateSimpleForm(true);

        $allowedExtensions = ['application/pdf', 'text/plain'];
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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.png';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForText(
            'Invalid file extension.',
            5,
            '.dz-error'
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedItemsLimitMustPass(AcceptanceTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 5);

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 5);

        $I->haveADummyFile('test.pdf', 2);
        $I->attachFile('input[type="file"]', 'generated/test.pdf');

        $I->waitForText(
            'Too many items would be uploaded.',
            5,
            '.dz-error'
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithLimitedFileSizeMustPass(AcceptanceTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 3);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 5);

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.pdf';
        $I->haveADummyFile($fileName, 7);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForText(
            'File is too large',
            5,
            '.dz-error'
        );
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testUploadFormWithFilesAsAttachment(AcceptanceTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml');

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

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $this->fillSimpleForm($testFormBuilder, $I);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));

        $I->waitForElement('.dz-success', 5);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 15, '.form-success-wrapper');

        $I->cantSeePropertyKeysInEmail($adminEmail, ['file_upload']);
        $I->cantSeeZipFileInPimcoreAssetsFromField($form, 'file_upload');
    }
}

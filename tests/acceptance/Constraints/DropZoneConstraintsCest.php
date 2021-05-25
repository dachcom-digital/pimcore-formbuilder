<?php

namespace DachcomBundle\Test\acceptance\Constraints;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class DropZoneConstraintsCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testEmptyFileUploadFormWithNotBlankConstraint(AcceptanceTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml', true);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

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
        $I->haveABootedSymfonyConfiguration('config_dropzonejs.yml', true);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('drop-zone', ['action' => 'dynamicMultiFile']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/drop-zone');

        $I->waitForElement('.dz-button', 5);

        $fileName = 'test.txt';
        $I->haveADummyFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));
        $I->waitForElement('.dz-success', 5);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForElementVisible('.form-success-wrapper', 15);
    }
}

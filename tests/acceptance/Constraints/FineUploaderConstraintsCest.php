<?php

namespace DachcomBundle\Test\acceptance\Constraints;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

/**
 * Class FineUploaderConstraints
 *
 * @package DachcomBundle\Test\acceptance\Constraints
 */
class FineUploaderConstraintsCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testEmptyFileUploadFormWithNotBlankConstraint(AcceptanceTester $I)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormField('dynamic_multi_file', 'file_upload', 'File Upload', ['dynamic_multi_file_not_blank'])
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

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

        $document = $I->haveAPageDocument('form-test', 'javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $I->waitForElement('div.qq-upload-button', 5);
        $I->waitForElement('input[type="file"]', 5);

        $fileName = 'test.txt';
        $I->haveFile($fileName, 1);
        $I->attachFile('input[type="file"]', sprintf('generated/%s', $fileName));
        $I->waitForElement('.qq-file-id-0.qq-upload-success', 15);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForElementVisible('.form-success-wrapper', 15);
    }
}

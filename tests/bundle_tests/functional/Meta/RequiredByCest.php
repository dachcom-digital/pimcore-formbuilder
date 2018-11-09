<?php

namespace DachcomBundle\Test\functional\Meta;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class RequiredByCest
{
    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testFormRequiredByDocumentsCest(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $dummyDocument = $I->haveAPageDocument('content-test-page');

        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');

        $I->haveAUser('form_tester');
        $I->amLoggedInAs('form_tester');
        $I->amOnPage('/admin/formbuilder/settings/get-form-dependencies?formId=1&page=1&start=0&limit=1');

        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'total'     => 1,
            'documents' => [
                [
                    'id'      => $document->getId(),
                    'type'    => 'document',
                    'subtype' => 'page',
                    'path'    => $document->getFullPath()
                ]
            ]
        ]);
    }
}

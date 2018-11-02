<?php

namespace DachcomBundle\Test\functional\Attributes;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class FormAttributesCest
{
    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testFormAttributes(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $testFormBuilder->addFormAttributes('class', 'awesome-class');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->seeElement('form.awesome-class');
    }
}

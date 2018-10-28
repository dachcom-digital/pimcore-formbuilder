<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class AbstractFieldCest
{
    protected $type;

    protected $name;

    protected $displayName;

    /**
     * @param FunctionalTester $I
     * @param array            $options
     * @param string           $formTemplate
     * @param array            $optional
     *
     * @return array
     */
    protected function setupField(FunctionalTester $I, $options = [], $optional = [], $formTemplate = 'form_div_layout.html.twig')
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                $this->type,
                $this->name,
                $this->displayName,
                [], $options, $optional
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, $formTemplate);
        $I->amOnPage('/form-test');

        return [$adminEmail, $testFormBuilder, $form];
    }
}

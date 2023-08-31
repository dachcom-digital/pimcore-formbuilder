<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\TestFormBuilder;

abstract class AbstractFieldCest
{
    protected $type;

    protected $subType;

    protected $name;

    protected $displayName;

    /**
     * @param FunctionalTester $I
     * @param array            $options
     * @param array            $optional
     * @param string           $formTemplate
     *
     * @return array
     */
    protected function setupField(FunctionalTester $I, $options = [], $optional = [], $formTemplate = 'form_div_layout.html.twig')
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))->setUseAjax(false);

        if ($this->type === 'container') {
            $subFields = isset($optional['subFields']) ? $optional['subFields'] : [];
            $testFormBuilder->addFormFieldContainer(
                $this->subType,
                $this->name,
                $options,
                $subFields
            );
        } else {
            $testFormBuilder->addFormField(
                $this->type,
                $this->name,
                $this->displayName,
                [], $options, $optional
            );
        }

        $testFormBuilder->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, $formTemplate);
        $I->amOnPage('/form-test');

        return [$adminEmail, $testFormBuilder, $form];
    }
}

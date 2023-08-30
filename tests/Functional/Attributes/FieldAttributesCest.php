<?php

namespace DachcomBundle\Test\Functional\Attributes;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class FieldAttributesCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testFieldAttribute(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text',
                [],
                [
                    'attr' => [
                        'readonly' => 'readonly'
                    ]
                ]
            )
            ->addFormFieldSubmitButton('submit');

        $testFormBuilder->addFormAttributes('class', 'awesome-class');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'text', '', '[readonly="readonly"]'));
    }

    /**
     * @param FunctionalTester $I
     */
    public function testFieldAttributes(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text',
                [],
                [
                    'attr' => [
                        'readonly' => 'readonly',
                        'title'    => 'this-is-a-title'
                    ]
                ]
            )
            ->addFormFieldSubmitButton('submit');

        $testFormBuilder->addFormAttributes('class', 'awesome-class');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'text', '', '[readonly="readonly"][title="this-is-a-title"]'));
    }

    /**
     * @param FunctionalTester $I
     */
    public function testFieldContainerAttribute(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'min'  => 1,
                    'attr' => [
                        'readonly' => 'readonly',
                        'title'    => 'this-is-a-title'
                    ]
                ],
                [
                    [
                        'type'         => 'text',
                        'name'         => 'sub_text_field',
                        'display_name' => 'Text Field',
                        'constraints'  => [],
                        'options'      => [],
                        'optional'     => [],
                    ]
                ]
            )
            ->addFormFieldContainer(
                'fieldset',
                'fieldset_container',
                [
                    'min'  => 1,
                    'attr' => [
                        'readonly' => 'readonly',
                        'title'    => 'this-is-a-title'
                    ]
                ],
                [
                    [
                        'type'         => 'text',
                        'name'         => 'sub_text_field_1',
                        'display_name' => 'Text Field 1',
                        'constraints'  => [],
                        'options'      => [],
                        'optional'     => [],
                    ]
                ]
            )
            ->addFormFieldSubmitButton('submit');

        $testFormBuilder->addFormAttributes('class', 'awesome-class');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'repeater_container', '', '[readonly="readonly"][title="this-is-a-title"]'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'fieldset_container', '', '[readonly="readonly"][title="this-is-a-title"]'));
    }
}

<?php

namespace DachcomBundle\Test\Functional\ConfigurationFlags;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class UseCustomRadioCheckboxCest
{
    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testUseCustomCheckboxEnabled(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $options = [
            'expanded'    => true,
            'multiple'    => true,
            'placeholder' => false,
            'label'       => 'Country Selection',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'choice',
                'choices',
                'Choices',
                [], $options, []
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->seeElement('div.custom-checkbox input', ['type' => 'checkbox']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testUseCustomCheckboxDisabled(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_configuration_flags.yaml');

        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $options = [
            'expanded'    => true,
            'multiple'    => true,
            'placeholder' => false,
            'label'       => 'Country Selection',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'choice',
                'choices',
                'Choices',
                [], $options, []
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->dontSeeElement('div.custom-checkbox input', ['type' => 'checkbox']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testUseCustomRadioEnabled(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $options = [
            'expanded'    => true,
            'multiple'    => false,
            'placeholder' => false,
            'label'       => 'Country Selection',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'choice',
                'choices',
                'Choices',
                [], $options, []
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->seeElement('div.custom-radio input', ['type' => 'radio']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testUseCustomRadioDisabled(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_configuration_flags.yaml');

        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $options = [
            'expanded'    => true,
            'multiple'    => false,
            'placeholder' => false,
            'label'       => 'Country Selection',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'choice',
                'choices',
                'Choices',
                [], $options, []
            )
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'bootstrap_4_layout.html.twig');
        $I->amOnPage('/form-test');

        $I->dontSeeElement('div.custom-radio input', ['type' => 'radio']);
    }
}

<?php

namespace DachcomBundle\Test\functional\ConfigurationFlags;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\TestFormBuilder;

class UseHoneyPotFieldCest
{
    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testHoneyPotFieldEnabled(FunctionalTester $I)
    {
        $document = $I->haveAPageDocument('form-test');

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);
        $I->amOnPage('/form-test');

        $I->seeElement('input', ['type' => 'text', 'name' => 'formbuilder_1[inputUserName]']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @return array
     */
    public function testHoneyPotFieldDisabled(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_configuration_flags.yml');

        $document = $I->haveAPageDocument('form-test');

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormField(
                'text',
                'text',
                'text')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);
        $I->amOnPage('/form-test');

        $I->dontSeeElement('input', ['type' => 'text', 'name' => 'formbuilder_1[inputUserName]']);
    }
}

<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class IbanConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultErrorMessage = 'This is not a valid International Bank Account Number (IBAN)';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.iban.message';

    /**
     * @param FunctionalTester $I
     */
    public function testIbanConstraintWithAllFieldsInvalid(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['iban']);

        $this->fillForm($I, ['text_0' => '123']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->defaultErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testIbanConstraintWithOverriddenMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['iban', ['message' => $this->customErrorMessage]]]);

        $this->fillForm($I, ['text_0' => '123']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        $I->seeKeyInFrontendTranslations($this->customErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testIbanConstraintWithAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['iban']);

        $this->fillForm($I, ['text_0' => 'AT022050302101023600']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

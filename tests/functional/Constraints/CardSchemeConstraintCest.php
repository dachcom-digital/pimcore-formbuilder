<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class CardSchemeConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultErrorMessage = 'Unsupported card type or invalid card number';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.card_scheme.message';

    /**
     * @param FunctionalTester $I
     */
    public function testCardSchemeConstraintWithMasterCardSchemeAndAllFieldsInvalid(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['card_scheme', ['schemes' => 'MASTERCARD']]]);

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
    public function testCardSchemeConstraintWithOverriddenMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['card_scheme', ['schemes' => 'MASTERCARD', 'message' => $this->customErrorMessage]]]);

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
    public function testCardSchemeConstraintWithMasterCardSchemeAndAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['card_scheme', ['schemes' => 'MASTERCARD']]]);

        $this->fillForm($I, ['text_0' => '5555555555554444']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class EmailConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultErrorMessage = 'This value is not a valid email address';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.email.message';

    /**
     * @param FunctionalTester $I
     */
    public function testEmailConstraintWithAllFieldsAndWrongValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['email']);

        $this->fillForm($I, ['text_0' => 'no_email']);

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
    public function testEmailConstraintWithOverriddenMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['email', ['message' => $this->customErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 'no_email']);

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
    public function testEmailConstraintWithAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['email']);

        $this->fillForm($I, ['text_0' => 'info@test.org']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

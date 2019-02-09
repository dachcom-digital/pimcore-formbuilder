<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class NotBlankConstraintCest extends AbstractConstraintCest
{
    /**
     * @var string
     */
    protected $defaultErrorMessage = 'This value should not be blank';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.not_blank.message';

    /**
     * @param FunctionalTester $I
     */
    public function testNotBlankConstraintWithAllFieldsLeavingEmpty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['not_blank']);

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
    public function testNotBlankConstraintWithOverriddenMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['not_blank', ['message' => $this->customErrorMessage]]]);

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
    public function testNotBlankConstraintWithAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['not_blank']);

        $this->fillForm($I);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

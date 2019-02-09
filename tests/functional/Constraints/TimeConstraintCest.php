<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class TimeConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultErrorMessage = 'This value is not a valid time';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.time.message';

    /**
     * @param FunctionalTester $I
     */
    public function testTimeConstraintWithAllFieldsInvalid(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['time']);

        $this->fillForm($I, ['text_0' => '15']);

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
    public function testTimeConstraintWithOverriddenMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['time', ['message' => $this->customErrorMessage]]]);

        $this->fillForm($I, ['text_0' => '15']);

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
    public function testTimeConstraintWithAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['time']);

        $this->fillForm($I, ['text_0' => '11:30:00']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

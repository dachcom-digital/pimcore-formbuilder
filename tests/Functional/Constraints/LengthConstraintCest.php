<?php

namespace DachcomBundle\Test\Functional\Constraints;

use DachcomBundle\Test\Support\FunctionalTester;

class LengthConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text', 'textarea', 'integer'];

    /**
     * @var string
     */
    protected $defaultMinErrorMessage = 'This value is too short. It should have 10 characters or more';

    /**
     * @var string
     */
    protected $defaultMaxErrorMessage = 'This value is too long. It should have 10 characters or less';

    /**
     * @var string
     */
    protected $defaultExactErrorMessage = 'This value should have exactly 10 characters';

    /**
     * @var string
     */
    protected $customMinErrorMessage = 'my.special.length.min.message';

    /**
     * @var string
     */
    protected $customMaxErrorMessage = 'my.special.length.max.message';

    /**
     * @var string
     */
    protected $customExactErrorMessage = 'my.special.length.exact.message';

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithAllFieldsAndMinValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['min' => 10]]]);

        $this->fillForm($I, ['text_0' => 'short', 'textarea_0' => 'short', 'integer_0' => 123]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->defaultMinErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithAllFieldsAndMaxValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['max' => 10]]]);

        $this->fillForm($I, ['text_0' => 'test_with_too_long_text', 'textarea_0' => 'test_with_too_long_text', 'integer_0' => 1234567891011]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->defaultMaxErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithAllFieldsAndExactValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['min' => 10, 'max' => 10]]]);

        $this->fillForm($I, ['text_0' => 'short', 'textarea_0' => 'short', 'integer_0' => 1234567891011]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->defaultExactErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithOverriddenMinMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['min' => 10, 'minMessage' => $this->customMinErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 'short', 'textarea_0' => 'short', 'integer_0' => 123]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customMinErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        //@fixme!
        //$I->seeKeyInFrontendTranslations($this->customMinErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithOverriddenMaxMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['max' => 10, 'maxMessage' => $this->customMaxErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 'test_with_too_long_text', 'textarea_0' => 'test_with_too_long_text', 'integer_0' => 1234567891011]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customMaxErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        //@fixme!
        //$I->seeKeyInFrontendTranslations($this->customMaxErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithOverriddenExactMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['min' => 10, 'max' => 10, 'exactMessage' => $this->customExactErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 'short', 'textarea_0' => 'short', 'integer_0' => 1234567891011]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customExactErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        //@fixme!
        //$I->seeKeyInFrontendTranslations($this->customExactErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testLengthConstraintWithAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['length', ['min' => 2, 'max' => 20]]]);

        $this->fillForm($I, ['text_0' => 'this_is_a_text', 'textarea_0' => 'this_is_a_text', 'integer_0' => 123]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

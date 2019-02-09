<?php

namespace DachcomBundle\Test\functional\Constraints;

use DachcomBundle\Test\FunctionalTester;

class RangeConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultMinErrorMessage = 'This value should be 10 or more';

    /**
     * @var string
     */
    protected $defaultMaxErrorMessage = 'This value should be 20 or less';

    /**
     * @var string
     */
    protected $defaultInvalidErrorMessage = 'This value should be a valid number';

    /**
     * @var string
     */
    protected $customMinErrorMessage = 'my.special.range.min.message';

    /**
     * @var string
     */
    protected $customMaxErrorMessage = 'my.special.range.max.message';

    /**
     * @var string
     */
    protected $customInvalidErrorMessage = 'my.special.range.exact.message';

    /**
     * @param FunctionalTester $I
     */
    public function testRangeConstraintWithAllFieldsAndMinValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 10, 'max' => 20]]]);

        $this->fillForm($I, ['text_0' => 5]);

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
    public function testRangeConstraintWithAllFieldsAndMaxValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 10, 'max' => 20]]]);

        $this->fillForm($I, ['text_0' => 30]);

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
    public function testRangeConstraintWithAllFieldsAndInvalidValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 10, 'max' => 20]]]);

        $this->fillForm($I, ['text_0' => 'text']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->defaultInvalidErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRangeConstraintWithOverriddenMinMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 10, 'max' => 20, 'minMessage' => $this->customMinErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 2]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customMinErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        //@fixme
        //$I->seeKeyInFrontendTranslations($this->customMinErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRangeConstraintWithOverriddenMaxMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 0, 'max' => 10, 'maxMessage' => $this->customMaxErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 12]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customMaxErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        //@fixme
        //$I->seeKeyInFrontendTranslations($this->customMaxErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRangeConstraintWithOverriddenInvalidMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 10, 'max' => 10, 'invalidMessage' => $this->customInvalidErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 'text']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->customInvalidErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }

        //@fixme
        //$I->seeKeyInFrontendTranslations($this->customInvalidErrorMessage);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRangeConstraintWithAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 2, 'max' => 20]]]);

        $this->fillForm($I, ['text_0' => 12]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

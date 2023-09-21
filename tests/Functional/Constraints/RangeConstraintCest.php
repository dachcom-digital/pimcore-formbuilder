<?php

namespace DachcomBundle\Test\Functional\Constraints;

use DachcomBundle\Test\Support\FunctionalTester;
use Symfony\Component\HttpKernel\Kernel;

class RangeConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultInvalidErrorMessage = 'This value should be a valid number';

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
    protected $defaultBetweenRangeErrorMessage = 'This value should be between 10 and 20';

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
    protected $customNotInRageErrorMessage = 'my.special.not_in_range.message';

    /**
     * @var string
     */
    protected $customInvalidErrorMessage = 'my.special.range.exact.message';

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
    public function testRangeConstraintWithAllFieldsAndMinValue(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', ['min' => 10, 'max' => 20]]]);

        $this->fillForm($I, ['text_0' => 5]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->getRangeMessage('min'), sprintf('//form[@name="formbuilder_1"]%s', $selector));
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
                $I->see($this->getRangeMessage('max'), sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRangeConstraintWithOverriddenMinMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', array_merge(['min' => 10, 'max' => 20], $this->getNotInRangeMessageConfig('min'))]]);

        $this->fillForm($I, ['text_0' => 2]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->getNotInRangeMessageMessage('min'), sprintf('//form[@name="formbuilder_1"]%s', $selector));
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
        list($testFormBuilder, $form) = $this->setupForm($I, [['range', array_merge(['min' => 0, 'max' => 10], $this->getNotInRangeMessageConfig('max'))]]);

        $this->fillForm($I, ['text_0' => 12]);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->getNotInRangeMessageMessage('max'), sprintf('//form[@name="formbuilder_1"]%s', $selector));
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

    /**
     * @param string $type
     *
     * @return array
     */
    protected function getNotInRangeMessageConfig($type = 'min')
    {
        if ($type === 'min') {
            return version_compare(Kernel::VERSION, '4.4.0', '>=')
                ? ['notInRangeMessage' => $this->customNotInRageErrorMessage]
                : ['minMessage' => $this->customMinErrorMessage];
        } elseif ($type === 'max') {
            return version_compare(Kernel::VERSION, '4.4.0', '>=')
                ? ['notInRangeMessage' => $this->customNotInRageErrorMessage]
                : ['maxMessage' => $this->customMaxErrorMessage];
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getNotInRangeMessageMessage($type = 'min')
    {
        if ($type === 'min') {
            return version_compare(Kernel::VERSION, '4.4.0', '>=') ? $this->customNotInRageErrorMessage : $this->customMinErrorMessage;
        } elseif ($type === 'max') {
            return version_compare(Kernel::VERSION, '4.4.0', '>=') ? $this->customNotInRageErrorMessage : $this->customMaxErrorMessage;
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getRangeMessage($type = 'min')
    {
        if ($type === 'min') {
            return version_compare(Kernel::VERSION, '4.4.0', '>=') ? $this->defaultBetweenRangeErrorMessage : $this->defaultMinErrorMessage;
        } elseif ($type === 'max') {
            return version_compare(Kernel::VERSION, '4.4.0', '>=') ? $this->defaultBetweenRangeErrorMessage : $this->defaultMaxErrorMessage;
        }
    }
}

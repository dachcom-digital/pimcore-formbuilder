<?php

namespace DachcomBundle\Test\Functional\Constraints;

use DachcomBundle\Test\Support\FunctionalTester;

class IpConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultErrorMessage = 'This is not a valid IP address';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.ip.message';

    /**
     * @param FunctionalTester $I
     */
    public function testIpConstraintWithVersion4AndAllFieldsInvalid(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['ip', ['version' => 4]]]);

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
    public function testIpConstraintWithVersion6AndAllFieldsInvalid(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['ip', ['version' => 6]]]);

        $this->fillForm($I, ['text_0' => '12001:0db8:85a3:0000:0000:8a2e:0370:7334']);

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
    public function testIpConstraintWithOverriddenMessageProperty(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['ip', ['version' => 4, 'message' => $this->customErrorMessage]]]);

        $this->fillForm($I, ['text_0' => 'text_value']);

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
    public function testIpConstraintWithVersion4AndAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['ip', ['version' => 4]]]);

        $this->fillForm($I, ['text_0' => '192.168.108.105']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testIpConstraintWithVersion6AndAllFieldsPopulated(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, [['ip', ['version' => 6]]]);

        $this->fillForm($I, ['text_0' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->see('Success!', '.message.message-success');
    }
}

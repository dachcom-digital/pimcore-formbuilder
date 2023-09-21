<?php

namespace DachcomBundle\Test\Functional\Constraints;

use DachcomBundle\Test\Support\FunctionalTester;

class AUrlConstraintCest extends AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = ['text'];

    /**
     * @var string
     */
    protected $defaultErrorMessage = 'This value is not a valid URL';

    /**
     * @var string
     */
    protected $customErrorMessage = 'my.special.url.message';

    /**
     * @param FunctionalTester $I
     */
    public function testUrlConstraintWithAllFieldsInvalid(FunctionalTester $I)
    {
        list($testFormBuilder, $form) = $this->setupForm($I, ['url']);

        $this->fillForm($I, ['text_0' => 'not_a_url']);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->dontSee('Success!', '.message.message-success');

        foreach ($this->getFieldsToTest() as $fields) {
            foreach ($fields['selector'] as $message => $selector) {
                $I->see($this->defaultErrorMessage, sprintf('//form[@name="formbuilder_1"]%s', $selector));
            }
        }
    }
}

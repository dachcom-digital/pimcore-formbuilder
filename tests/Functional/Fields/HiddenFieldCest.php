<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class HiddenFieldCest extends AbstractFieldCest
{
    protected $type = 'hidden';

    protected $name = 'hidden_field';

    protected $displayName = 'hidden_field';

    /**
     * @param FunctionalTester $I
     */
    public function testHiddenField(FunctionalTester $I)
    {
        $options = [
            'data' => 'PRE_FILLED'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_hidden_field', ['type' => 'hidden']);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'hidden_field'), 'hidden field data');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->seePropertiesInEmail($adminEmail, ['hidden_field' => 'hidden field data']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function testHiddenFieldOptionsWithData(FunctionalTester $I)
    {
        $options = [
            'data' => 'PRE_FILLED'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_hidden_field', ['value' => 'PRE_FILLED', 'type' => 'hidden']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testHiddenFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'help_text' => 'This is a Helptext'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testHiddenFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [];

        $optionals = [
            'email_label' => 'Text Email Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'hidden_field'), 'hidden field data');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Text Email Field');
    }
}

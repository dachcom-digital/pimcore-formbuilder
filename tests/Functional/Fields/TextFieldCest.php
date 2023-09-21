<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class TextFieldCest extends AbstractFieldCest
{
    protected $type = 'text';
    protected $name = 'simple_text';
    protected $displayName = 'simple_text';

    /**
     * @param FunctionalTester $I
     */
    public function testTextField(FunctionalTester $I)
    {
        $options = [];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_text');

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text'), 'Simple Text Data');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_text' => 'Simple Text Data']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTextFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'Text Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Text Field', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTextFieldOptionsWithData(FunctionalTester $I)
    {
        $options = [
            'data' => 'PRE_FILLED'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_text', ['value' => 'PRE_FILLED']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTextFieldOptionsWithHelpText(FunctionalTester $I)
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
    public function testTextFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [];

        $optionals = [
            'email_label' => 'Text Email Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text'), 'SIMPLE_TEXT');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Text Email Field');
    }
}

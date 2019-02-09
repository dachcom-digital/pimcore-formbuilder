<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

class CheckboxFieldCest extends AbstractFieldCest
{
    protected $type = 'checkbox';

    protected $name = 'checkbox_field';

    protected $displayName = 'checkbox_field';

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxField(FunctionalTester $I)
    {
        $options = [];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_checkbox_field');

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkbox_field'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['checkbox_field' => '1']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'Checkbox Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Checkbox Field', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxFieldOptionsWithPreCheck(FunctionalTester $I)
    {
        $options = [
            'data' => true
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeCheckboxIsChecked('input#formbuilder_1_checkbox_field');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxFieldOptionsWithSpecialValue(FunctionalTester $I)
    {
        $options = [
            'value' => 'special_checkbox'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkbox_field'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['checkbox_field' => '1']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxFieldOptionsWithSpecialValueAndPreCheck(FunctionalTester $I)
    {
        $options = [
            'value' => 'special_checkbox',
            'data'  => true
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeCheckboxIsChecked('input#formbuilder_1_checkbox_field');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxFieldOptionsWithHelpText(FunctionalTester $I)
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
    public function testCheckboxFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [];

        $optionals = [
            'email_label' => 'Checkbox Email Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkbox_field'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Checkbox Email Field');
    }
}

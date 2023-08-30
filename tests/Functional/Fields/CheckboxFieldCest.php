<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

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

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxLabelTranslationDefault(FunctionalTester $I)
    {
        $translatedLabel = 'Translated Checkbox Field 1';

        $options = [
            'label' => 'formbuilder.label.checkbox_field_1'
        ];

        $I->haveAFrontendTranslatedKey('formbuilder.label.checkbox_field_1', $translatedLabel, 'en');

        $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_checkbox_field');
        $I->seeKeyInFrontendTranslations('formbuilder.label.checkbox_field_1');
        $I->see($translatedLabel, 'label[for="formbuilder_1_checkbox_field"]');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCheckboxLabelTranslationWithHtml(FunctionalTester $I)
    {
        $translatedLabel = 'Translated <a href="/test-link">Checkbox Field 1 with Link!</a>';

        $options = [
            'label' => 'formbuilder.label.checkbox_field_1'
        ];

        $I->haveAFrontendTranslatedKey('formbuilder.label.checkbox_field_1', $translatedLabel, 'en');

        $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_checkbox_field');
        $I->seeKeyInFrontendTranslations('formbuilder.label.checkbox_field_1');
        $I->see($translatedLabel, 'label[for="formbuilder_1_checkbox_field"]');
    }

}

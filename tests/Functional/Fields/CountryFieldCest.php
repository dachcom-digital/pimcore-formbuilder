<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class CountryFieldCest extends AbstractFieldCest
{
    protected $type = 'country';

    protected $name = 'country_field';

    protected $displayName = 'country_field';

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'label'       => 'Country Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_country_field');
        $I->see('Country Selection', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'help_text'   => 'This is a Helptext'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionsWithDataOnDropDown(FunctionalTester $I)
    {
        $options = [
            'data'        => ['AT'],
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeOptionIsSelected('select#formbuilder_1_country_field', 'Austria');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionsWithDataOnCheckboxes(FunctionalTester $I)
    {
        $options = [
            'data'        => ['AT', 'CH'],
            'expanded'    => true,
            'multiple'    => true,
            'placeholder' => false,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeCheckboxIsChecked('input[value="AT"]');
        $I->seeCheckboxIsChecked('input[value="CH"]');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionsWithPreferredChoices(FunctionalTester $I)
    {
        $options = [
            'expanded'          => false,
            'multiple'          => false,
            'placeholder'       => false,
            'preferred_choices' => [
                'AT'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('Austria', 'select#formbuilder_1_country_field option:first-child');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionsWithPlaceholder(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => 'Please Select'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('Please Select', 'select#formbuilder_1_country_field option:first-child');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'expanded' => false,
            'multiple' => false
        ];

        $optionals = [
            'email_label' => 'Country Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->selectOption('select#formbuilder_1_country_field', 'AT');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Country Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldSubmission(FunctionalTester $I)
    {
        $options = [
            'expanded' => false,
            'multiple' => false,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->selectOption('select#formbuilder_1_country_field', 'AT');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['country_field' => 'Austria']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testCountryFieldSubmissionWithMultipleSelections(FunctionalTester $I)
    {
        $options = [
            'expanded' => true,
            'multiple' => true,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->checkOption('input[type="checkbox"][value="AT"]');
        $I->checkOption('input[type="checkbox"][value="CH"]');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['country_field' => 'Austria<br>Switzerland']);
    }
}

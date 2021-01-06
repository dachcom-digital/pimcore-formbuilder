<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\Services\TestSimpleDynamicChoices;
use DachcomBundle\Test\FunctionalTester;

class DynamicChoiceFieldCest extends AbstractFieldCest
{
    protected $type = 'dynamic_choice';

    protected $name = 'dynamic_choice_field';

    protected $displayName = 'dynamic_choice_field';

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'label'       => 'Dynamic Choice Selection',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_dynamic_choice_field');
        $I->see('Dynamic Choice Selection', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'help_text'   => 'This is a Helptext',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithDataOnDropDown(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'data'        => [2],
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeOptionIsSelected('select#formbuilder_1_dynamic_choice_field', 'Entity 2');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithDataOnRadios(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'data'        => [2],
            'expanded'    => true,
            'multiple'    => false,
            'placeholder' => false,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeOptionIsSelected('input[type="radio"]#formbuilder_1_dynamic_choice_field_1', '2');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithDataOnCheckboxes(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'data'        => [2, 3],
            'expanded'    => true,
            'multiple'    => true,
            'placeholder' => false,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeCheckboxIsChecked('input[value="2"]');
        $I->seeCheckboxIsChecked('input[value="3"]');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithPreferredChoices(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded'          => false,
            'multiple'          => false,
            'placeholder'       => false,
            'preferred_choices' => [
                2
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('Entity 2', 'select#formbuilder_1_dynamic_choice_field option:first-child');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionsWithPlaceholder(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => 'Please Select',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('Please Select', 'select#formbuilder_1_dynamic_choice_field option:first-child');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded' => false,
            'multiple' => false,
        ];

        $optionals = [
            'email_label' => 'Dynamic Choice Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->selectOption('select#formbuilder_1_dynamic_choice_field', 2);
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Dynamic Choice Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldSubmission(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded' => false,
            'multiple' => false,

        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->selectOption('select#formbuilder_1_dynamic_choice_field', 1);
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['dynamic_choice_field' => 'Entity 1']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceFieldSubmissionWithMultipleSelections(FunctionalTester $I)
    {
        $options = [
            'service' => TestSimpleDynamicChoices::class,
            'expanded' => true,
            'multiple' => true,
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->checkOption('input[type="checkbox"][value="2"]');
        $I->checkOption('input[type="checkbox"][value="3"]');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['dynamic_choice_field' => 'Entity 2, Entity 3']);
    }
}

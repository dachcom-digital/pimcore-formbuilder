<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

class ChoiceFieldCest extends AbstractFieldCest
{
    protected $type = 'choice';

    protected $name = 'choice_field';

    protected $displayName = 'choice_field';

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'label'       => 'Choice Selection',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_choice_field');
        $I->see('Choice Selection', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'help_text'   => 'This is a Helptext',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithDataOnDropDown(FunctionalTester $I)
    {
        $options = [
            'data'        => ['choice_b'],
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeOptionIsSelected('select#formbuilder_1_choice_field', 'Choice B');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithDataOnRadios(FunctionalTester $I)
    {
        $options = [
            'data'        => ['choice_b'],
            'expanded'    => true,
            'multiple'    => false,
            'placeholder' => false,
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeOptionIsSelected('input[type="radio"]#formbuilder_1_choice_field_1', 'choice_b');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithDataOnCheckboxes(FunctionalTester $I)
    {
        $options = [
            'data'        => ['choice_b', 'choice_d'],
            'expanded'    => true,
            'multiple'    => true,
            'placeholder' => false,
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b',
                'Choice C' => 'choice_c',
                'Choice D' => 'choice_d',
                'Choice E' => 'choice_e',
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeCheckboxIsChecked('input[value="choice_b"]');
        $I->seeCheckboxIsChecked('input[value="choice_d"]');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithPreferredChoices(FunctionalTester $I)
    {
        $options = [
            'expanded'          => false,
            'multiple'          => false,
            'placeholder'       => false,
            'preferred_choices' => [
                'choice_b'
            ],
            'choices'           => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('Choice B', 'select#formbuilder_1_choice_field option:first-child');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithPlaceholder(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => 'Please Select',
            'choices'     => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('Please Select', 'select#formbuilder_1_choice_field option:first-child');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'expanded' => false,
            'multiple' => false,
            'choices'  => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        $optionals = [
            'email_label' => 'Choice Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->selectOption('select#formbuilder_1_choice_field', 'choice_b');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Choice Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldOptionsWithOptGroups(FunctionalTester $I)
    {
        $options = [
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'choices'     => [
                'Group A' => [
                    'Choice A' => 'choice_a',
                    'Choice B' => 'choice_b'
                ],
                'Group B' => [
                    'Choice C' => 'choice_c',
                    'Choice D' => 'choice_d'
                ],
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->seeElement('#formbuilder_1_choice_field optgroup[label="Group A"]');
        $I->seeElement('#formbuilder_1_choice_field optgroup[label="Group A"] option[value="choice_b"]');

        $I->seeElement('#formbuilder_1_choice_field optgroup[label="Group B"]');
        $I->seeElement('#formbuilder_1_choice_field optgroup[label="Group B"] option[value="choice_c"]');

        $I->selectOption('select#formbuilder_1_choice_field', 'choice_b');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['choice_field' => 'Choice B']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldSubmission(FunctionalTester $I)
    {
        $options = [
            'expanded' => false,
            'multiple' => false,
            'choices'  => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->selectOption('select#formbuilder_1_choice_field', 'choice_a');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['choice_field' => 'Choice A']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testChoiceFieldSubmissionWithMultipleSelections(FunctionalTester $I)
    {
        $options = [
            'expanded' => true,
            'multiple' => true,
            'choices'  => [
                'Choice A' => 'choice_a',
                'Choice B' => 'choice_b',
                'Choice C' => 'choice_c'
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->checkOption('input[type="checkbox"][value="choice_b"]');
        $I->checkOption('input[type="checkbox"][value="choice_c"]');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['choice_field' => 'Choice B<br>Choice C']);
    }
}

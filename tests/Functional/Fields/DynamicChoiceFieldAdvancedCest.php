<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\Services\TestAdvancedDynamicChoices;
use DachcomBundle\Test\Support\FunctionalTester;

class DynamicChoiceFieldAdvancedCest extends AbstractFieldCest
{
    protected $type = 'dynamic_choice';

    protected $name = 'dynamic_choice_advanced_field';

    protected $displayName = 'dynamic_choice_advanced_field';

    /**
     * @param FunctionalTester $I
     */
    public function testDynamicChoiceAdvancedField(FunctionalTester $I)
    {
        $options = [
            'service' => TestAdvancedDynamicChoices::class,
            'expanded'    => false,
            'multiple'    => false,
            'placeholder' => false,
            'label'       => 'Advanced Dynamic Choice Selection',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_dynamic_choice_advanced_field');

        // custom choice attr
        $I->seeElement('select#formbuilder_1_dynamic_choice_advanced_field option.special-choice-class');
        // custom group by
        $I->seeElement('select#formbuilder_1_dynamic_choice_advanced_field optgroup[label="Group A"]');
        // custom choice value
        $I->seeElement('select#formbuilder_1_dynamic_choice_advanced_field optgroup[label="Group A"] option[value="1-custom-value"]');
        // custom preferred choices
        $I->seeElement('select optgroup:first-child option[value="5-custom-value"]');
        // custom choice label
        $I->seeElement('//option[text()="Entity 1 Custom Label"]');
    }
}

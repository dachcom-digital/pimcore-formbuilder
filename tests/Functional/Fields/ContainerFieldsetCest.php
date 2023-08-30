<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class ContainerFieldsetCest extends AbstractFieldCest
{
    protected $type = 'container';

    protected $subType = 'fieldset';

    protected $name = 'fieldset_container';

    /**
     * @param FunctionalTester $I
     */
    public function testFieldSetContainerFieldWithClass(FunctionalTester $I)
    {
        $this->setupField($I);
        $I->seeElement('.formbuilder-container.formbuilder-container-fieldset');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testFieldSetContainerFieldEmailProperty(FunctionalTester $I)
    {
        $options = [];
        $optional = [
            'subFields' => [
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field_1',
                    'display_name' => 'Text Field 1',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ],
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field_2',
                    'display_name' => 'Text Field 2',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ]
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optional);

        $repeaterSelector = $testFormBuilder->getFormFieldSelector(1, 'fieldset_container');
        $fieldGenerator = function ($index, $subIndex) {
            return sprintf('input#formbuilder_1_fieldset_container_%d_sub_text_field_%d', $index, $subIndex);
        };

        $I->fillField(sprintf('%s %s', $repeaterSelector, $fieldGenerator(0, 1)), 'text_value_1');
        $I->fillField(sprintf('%s %s', $repeaterSelector, $fieldGenerator(0, 2)), 'text_value_2');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, [
            'fieldset_container_sub_text_field_1' => 'text_value_1',
            'fieldset_container_sub_text_field_2' => 'text_value_2',
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testFieldSetContainerFieldWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'Container FieldSet Section'
        ];

        $this->setupField($I, $options);
        $I->see('Container FieldSet Section', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testFieldSetContainerFieldBlockLabel(FunctionalTester $I)
    {
        $options = [];
        $optional = [
            'subFields' => [
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field',
                    'display_name' => 'Text Field',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ]
            ]
        ];

        $this->setupField($I, $options, $optional);

        $I->dontSeeElement('.formbuilder-container.formbuilder-container-fieldset[data-prototype]');
        $I->seeElement('.formbuilder-container.formbuilder-container-fieldset input#formbuilder_1_fieldset_container_0_sub_text_field');
    }
}

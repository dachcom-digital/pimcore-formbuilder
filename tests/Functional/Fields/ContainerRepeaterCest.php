<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class ContainerRepeaterCest extends AbstractFieldCest
{
    protected $type = 'container';

    protected $subType = 'repeater';

    protected $name = 'repeater_container';

    /**
     * @param FunctionalTester $I
     */
    public function testRepeaterContainerFieldWithClass(FunctionalTester $I)
    {
        $this->setupField($I);
        $I->seeElement('.formbuilder-container.formbuilder-container-repeater');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRepeaterContainerFieldEmailProperty(FunctionalTester $I)
    {
        $options = [
            'min' => 2
        ];
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

        $repeaterSelector = $testFormBuilder->getFormFieldSelector(1, 'repeater_container');
        $fieldGenerator = static function ($index, $subIndex) {
            return sprintf('input#formbuilder_1_repeater_container_%d_sub_text_field_%d', $index, $subIndex);
        };

        $I->fillField(sprintf('%s %s', $repeaterSelector, $fieldGenerator(0, 1)), 'text_value_1');
        $I->fillField(sprintf('%s %s', $repeaterSelector, $fieldGenerator(0, 2)), 'text_value_2');
        $I->fillField(sprintf('%s %s', $repeaterSelector, $fieldGenerator(1, 1)), 'text_value_3');
        $I->fillField(sprintf('%s %s', $repeaterSelector, $fieldGenerator(1, 2)), 'text_value_4');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, [
            'repeater_container_0_sub_text_field_1' => 'text_value_1',
            'repeater_container_0_sub_text_field_2' => 'text_value_2',
            'repeater_container_1_sub_text_field_1' => 'text_value_3',
            'repeater_container_1_sub_text_field_2' => 'text_value_4'
        ]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRepeaterContainerFieldWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'Container Repeater Section'
        ];

        $this->setupField($I, $options);
        $I->see('Container Repeater Section', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRepeaterContainerFieldBlockLabel(FunctionalTester $I)
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

        $I->seeElement('.formbuilder-container.formbuilder-container-repeater[data-prototype]');
        $I->dontSeeElement('.formbuilder-container.formbuilder-container-repeater input#formbuilder_1_repeater_container_0_sub_text_field');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRepeaterContainerFieldMinAmount(FunctionalTester $I)
    {
        $options = [
            'min' => 1
        ];

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

        $I->seeElement('.formbuilder-container.formbuilder-container-repeater', ['data-repeater-min' => '1']);
        $I->seeElement('.formbuilder-container.formbuilder-container-repeater input#formbuilder_1_repeater_container_0_sub_text_field');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testRepeaterContainerFieldMaxAmount(FunctionalTester $I)
    {
        $options = [
            'max' => 1
        ];

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

        $I->seeElement('.formbuilder-container.formbuilder-container-repeater', ['data-repeater-max' => '1']);
        $I->dontSeeElement('.formbuilder-container.formbuilder-container-repeater input#formbuilder_1_repeater_container_0_sub_text_field');
    }

}

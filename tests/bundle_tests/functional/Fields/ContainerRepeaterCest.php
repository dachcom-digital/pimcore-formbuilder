<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

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

        $I->seeElement('.formbuilder-container.formbuilder-container-repeater', ['data-repeater-min' => 1]);
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

        $I->seeElement('.formbuilder-container.formbuilder-container-repeater', ['data-repeater-max' => 1]);
        $I->dontSeeElement('.formbuilder-container.formbuilder-container-repeater input#formbuilder_1_repeater_container_0_sub_text_field');
    }

}

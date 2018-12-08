<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

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

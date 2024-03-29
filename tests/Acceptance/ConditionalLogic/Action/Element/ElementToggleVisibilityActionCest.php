<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Action\Element;

use DachcomBundle\Test\Acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\Support\AcceptanceTester;
use DachcomBundle\Test\Support\Util\TestFormBuilder;

/**
 * Action "toggleElement". Must work on:
 * - all fields
 */
class ElementToggleVisibilityActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementVisibilityShow(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2'],
            'state'  => 'show'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsVisibilityShow(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'state'  => 'show'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', 'div.fb-cl-hide-element'), 5);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', 'div.fb-cl-hide-element'), 5);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 5);
        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerElementVisibilityShow(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['repeater_container'],
            'state'  => 'show'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'min' => 1
                ],
                [
                    [
                        'type'         => 'text',
                        'name'         => 'sub_text_field',
                        'display_name' => 'Text Field',
                        'constraints'  => [],
                        'options'      => [],
                        'optional'     => [],
                    ]
                ]
            );
            return $testFormBuilder;
        });

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'repeater_container', 'div.fb-cl-hide-element'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'repeater_container', 'div.fb-cl-hide-element'), 5);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'repeater_container'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerElementVisibilityHide(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['repeater_container'],
            'state'  => 'hide'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldContainer(
                'repeater',
                'repeater_container',
                [
                    'min' => 1
                ],
                [
                    [
                        'type'         => 'text',
                        'name'         => 'sub_text_field',
                        'display_name' => 'Text Field',
                        'constraints'  => [],
                        'options'      => [],
                        'optional'     => [],
                    ]
                ]
            );
            return $testFormBuilder;
        });

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, 'repeater_container'), 5);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, 'repeater_container', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testContainerFieldsetSubElementVisibilityShow(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['sub_text_field'],
            'state'  => 'show'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions], function (TestFormBuilder $testFormBuilder) {
            $testFormBuilder->addFormFieldContainer(
                'fieldset',
                'fieldset_container',
                [
                    'min' => 1
                ],
                [
                    [
                        'type'         => 'text',
                        'name'         => 'sub_text_field',
                        'display_name' => 'Text Field',
                        'constraints'  => [],
                        'options'      => [],
                        'optional'     => [],
                    ]
                ]
            );

            return $testFormBuilder;
        });

        $name = sprintf('%s_0_%s', 'fieldset_container', 'sub_text_field');

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, $name, 'div.fb-cl-hide-element'), 5);
        $I->waitForElementNotVisible($testFormBuilder->getFormFieldSelector(1, $name, 'div.fb-cl-hide-element'), 5);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElementVisible($testFormBuilder->getFormFieldSelector(1, $name), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementVisibilityHide(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2'],
            'state'  => 'hide'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testMultipleElementsVisibilityHide(AcceptanceTester $I)
    {
        $actions = [
            'type'   => 'toggleElement',
            'fields' => ['simple_text_input_2', 'simple_text_input_3'],
            'state'  => 'hide'
        ];

        $testFormBuilder = $this->runTestWithActions($I, [$actions]);

        $this->triggerCondition($I, $testFormBuilder);

        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2', 'div.fb-cl-hide-element'), 5);
        $I->waitForElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3', 'div.fb-cl-hide-element'), 5);
    }

}

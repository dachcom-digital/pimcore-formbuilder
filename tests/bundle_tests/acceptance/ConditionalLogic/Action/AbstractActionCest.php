<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use Pimcore\Model\Document\Email;

abstract class AbstractActionCest
{
    /**
     * @var array
     */
    protected $conditions = [
        [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_value',
            'value'      => 'text1'
        ]
    ];

    /**
     * @param AcceptanceTester $I
     * @param array            $actions
     * @param TestFormBuilder  $testFormBuilder
     *
     * @return TestFormBuilder
     */
    protected function runTestWithActionAndCustomFormBuilder(AcceptanceTester $I, array $actions, TestFormBuilder $testFormBuilder)
    {
        $document = $I->haveAPageDocument('form-test', 'javascript');

        $testFormBuilder->addFormConditionBlock($this->conditions, $actions);

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');

        return $testFormBuilder;
    }

    /**
     * @param AcceptanceTester $I
     * @param array            $actions
     * @param null             $closure
     * @param bool             $addConstraints
     * @param Email|null       $mailTemplate
     * @param Email|null       $copyMailTemplate
     * @param string           $locale
     *
     * @return TestFormBuilder|mixed
     */
    protected function runTestWithActions(
        AcceptanceTester $I,
        array $actions,
        $closure = null,
        $addConstraints = false,
        $mailTemplate = null,
        $copyMailTemplate = null,
        $locale = 'en'
    ) {
        $document = $I->haveAPageDocument('form-test', 'javascript', null, $locale);

        $testFormBuilder = $this->generateDefaultForm($actions, $addConstraints, $closure);

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, $copyMailTemplate, $formTemplate);
        $I->amOnPage('/form-test');

        return $testFormBuilder;
    }

    /**
     * @param AcceptanceTester $I
     * @param TestFormBuilder  $testFormBuilder
     */
    protected function triggerCondition(AcceptanceTester $I, TestFormBuilder $testFormBuilder)
    {
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');
        // just to trigger javascript blur we need to fill another field (input_4)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'), '');
    }

    /**
     * @param $actions
     * @param $addConstraints
     * @param $closure
     *
     * @return TestFormBuilder|mixed
     */
    protected function generateDefaultForm($actions, $addConstraints, $closure)
    {
        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldChoice('simple_dropdown', [
                'Simple DropDown Value 0' => 'simple_drop_down_value_0',
                'Simple DropDown Value 1' => 'simple_drop_down_value_1'
            ], [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldChoiceMultiple('multiple_select', [
                'Select 0' => 'select_0',
                'Select 1' => 'select_1',
                'Select 2' => 'select_2',
                'Select 3' => 'select_3'
            ], [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldInput('simple_text_input_1', [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldInput('simple_text_input_2', [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldInput('simple_text_input_3', [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldInput('simple_text_input_4', [], [], ($addConstraints ? ['not_blank', 'email'] : []))
            ->addFormFieldChoiceExpandedAndMultiple('checkboxes', [
                'Check 0' => 'check0',
                'Check 1' => 'check1',
                'Check 2' => 'check2',
                'Check 3' => 'check3',
            ], [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldChoiceExpanded('radios', [
                'Radio 0' => 'radio0',
                'Radio 1' => 'radio1',
                'Radio 2' => 'radio2',
                'Radio 3' => 'radio3',
            ], [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldTextArea('simple_text_area', [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldSingleCheckbox('single_checkbox', [], [], ($addConstraints ? ['not_blank'] : []))
            ->addFormFieldSubmitButton('submit')
            ->addFormConditionBlock($this->conditions, $actions);

        // allow modification
        if ($closure instanceof \Closure) {
            $testFormBuilder = $closure($testFormBuilder);
        }

        return $testFormBuilder;
    }
}

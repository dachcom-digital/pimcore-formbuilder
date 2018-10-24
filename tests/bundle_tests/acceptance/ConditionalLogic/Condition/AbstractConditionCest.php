<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;

abstract class AbstractConditionCest
{
    protected $action;

    /**
     * @param AcceptanceTester $I
     * @param array            $condition
     * @param TestFormBuilder  $testFormBuilder
     *
     * @return TestFormBuilder
     */
    protected function runTestWithConditionAndCustomFormBuilder(AcceptanceTester $I, array $condition, TestFormBuilder $testFormBuilder)
    {
        $document = $I->haveAPageDocument('form-test', 'javascript');

        $testFormBuilder->addFormConditionBlock([$condition], [$this->action]);

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        return $testFormBuilder;
    }

    /**
     * @param AcceptanceTester $I
     * @param array            $condition
     * @param null|\Closure    $closure
     *
     * @return TestFormBuilder|mixed
     */
    protected function runTestWithCondition(AcceptanceTester $I, array $condition, $closure = null)
    {
        $document = $I->haveAPageDocument('form-test', 'javascript');

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldChoice('simple_dropdown', [
                'Simple DropDown Value 0' => 'simple_drop_down_value_0',
                'Simple DropDown Value 1' => 'simple_drop_down_value_1'
            ])
            ->addFormFieldChoiceMultiple('multiple_select', [
                'Select 0' => 'select_0',
                'Select 1' => 'select_1',
                'Select 2' => 'select_2',
                'Select 3' => 'select_3'
            ])
            ->addFormFieldInput('simple_text_input_1', [], [], ['not_blank'])
            ->addFormFieldInput('simple_text_input_2', [], [], ['not_blank'])
            ->addFormFieldInput('simple_text_input_3')
            ->addFormFieldInput('simple_text_input_4', [], [], ['not_blank', 'email'])
            ->addFormFieldChoiceExpandedAndMultiple('checkboxes', [
                'Check 0' => 'check0',
                'Check 1' => 'check1',
                'Check 2' => 'check2',
                'Check 3' => 'check3',
            ])
            ->addFormFieldChoiceExpanded('radios', [
                'Radio 0' => 'radio0',
                'Radio 1' => 'radio1',
                'Radio 2' => 'radio2',
                'Radio 3' => 'radio3',
            ])
            ->addFormFieldTextArea('simple_text_area', [], [], ['not_blank'])
            ->addFormFieldSingleCheckbox('single_checkbox')
            ->addFormFieldSubmitButton('submit')
            ->addFormConditionBlock([$condition], [$this->action]);

        // allow modification
        if ($closure instanceof \Closure) {
            $testFormBuilder = $closure($testFormBuilder);
        }

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        return $testFormBuilder;
    }
}

<?php

namespace DachcomBundle\Test\HAcceptance\ConditionalLogic\Condition;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use Pimcore\Model\Document\Email;

abstract class AbstractActionCest
{
    protected array $conditions = [
        [
            'type'       => 'elementValue',
            'fields'     => ['simple_text_input_1'],
            'comparator' => 'is_value',
            'value'      => 'text1'
        ]
    ];

    protected function runTestWithActionAndCustomFormBuilder(AcceptanceTester $I, array $actions, TestFormBuilder $testFormBuilder): TestFormBuilder
    {
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder->addFormConditionBlock($this->conditions, $actions);

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, null, null, $formTemplate);
        $I->amOnPage('/form-test');

        return $testFormBuilder;
    }

    protected function runTestWithActions(
        AcceptanceTester $I,
        array $actions,
        ?\Closure $closure = null,
        bool $addConstraints = false,
        ?Email $mailTemplate = null,
        ?Email $additionalMailTemplate = null,
        string $locale = 'en'
    ): TestFormBuilder {
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction'], $locale);

        $testFormBuilder = $this->generateDefaultForm($actions, $addConstraints, $closure);

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, $additionalMailTemplate, $formTemplate);
        $I->amOnPage('/form-test');

        return $testFormBuilder;
    }

    protected function triggerCondition(AcceptanceTester $I, TestFormBuilder $testFormBuilder)
    {
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');
        // just to trigger javascript blur we need to fill another field (input_4)
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'), '');
    }

    protected function generateDefaultForm(array $actions, bool $addConstraints, ?\Closure $closure = null): TestFormBuilder
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

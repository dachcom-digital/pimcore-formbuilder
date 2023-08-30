<?php

namespace DachcomBundle\Test\Functional\ConditionalLogic\Action\Form;

use DachcomBundle\Test\Support\Helper\Traits;
use DachcomBundle\Test\Support\FunctionalTester;

class FormSwitchOutputWorkflowActionCest
{
    use Traits\FunctionalFormTrait;

    public function testSwitchingOutputWorkflow(FunctionalTester $I)
    {
        $outputWorkflowTitle = uniqid('Conditional WF', true);

        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $conditionalChannelMail = $I->haveAEmailDocumentForAdmin(['to' => 'conditional-recepient@test.org']);

        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $conditionalChannelDefinitions = [
            [
                'type'  => 'email',
                'email' => $conditionalChannelMail
            ]
        ];

        $workflow = $I->haveAOutputWorkflow($outputWorkflowTitle, $form, $conditionalChannelDefinitions, $outputWorkflowTitle);

        $conditions = [
            [
                'type'       => 'elementValue',
                'fields'     => ['simple_text_input_1'],
                'comparator' => 'is_value',
                'value'      => 'text1'
            ]
        ];

        $actions = [
            [
                'type'       => 'switchOutputWorkflow',
                'workflowId' => $workflow->getId(),
            ]
        ];

        $testFormBuilder->addFormConditionBlock($conditions, $actions);

        $I->updateAForm($form, $testFormBuilder);
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);

        // trigger condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsNotSent($adminEmail);
        $I->seeEmailIsSentTo('conditional-recepient@test.org', $conditionalChannelMail);
    }
}

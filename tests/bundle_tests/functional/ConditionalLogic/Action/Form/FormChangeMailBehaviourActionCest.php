<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Action\Form;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;

/**
 * Action "mailBehaviour"
 */
class FormChangeMailBehaviourActionCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeMailBehaviourRecipientForAdmin(FunctionalTester $I)
    {
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
                'type'       => 'mailBehaviour',
                'identifier' => 'recipient',
                'mailType'   => 'main',
                'value'      => 'conditional-recepient-main@test.org'
            ]
        ];

        $testFormBuilder = $this->generateSimpleForm();
        $testFormBuilder->addFormConditionBlock($conditions, $actions);

        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        // trigger condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSentTo('conditional-recepient-main@test.org', $adminEmail);

    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeMailBehaviourRecipientForUser(FunctionalTester $I)
    {
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
                'type'       => 'mailBehaviour',
                'identifier' => 'recipient',
                'mailType'   => 'copy',
                'value'      => 'conditional-recepient-copy@test.org'
            ]
        ];

        $testFormBuilder = $this->generateSimpleForm();
        $testFormBuilder->addFormConditionBlock($conditions, $actions);

        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $userEmail = $I->haveAEmailDocumentForUser();

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, $userEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        // trigger condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSentTo('recpient@test.org', $adminEmail);
        $I->seeEmailIsSentTo('conditional-recepient-copy@test.org', $userEmail);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeMailBehaviourMailTemplateForAdmin(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $adminConditionEmail = $I->haveAEmailDocumentForAdmin(['to' => 'custom-admin-mail@test.org']);

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
                'type'       => 'mailBehaviour',
                'identifier' => 'mailTemplate',
                'mailType'   => 'main',
                'value'      => [
                    'en' => [
                        'id'   => $adminConditionEmail->getId(),
                        'type' => 'document'
                    ],
                    'de' => [
                        'id'   => null,
                        'type' => null
                    ]
                ]
            ]
        ];

        $testFormBuilder = $this->generateSimpleForm();
        $testFormBuilder->addFormConditionBlock($conditions, $actions);

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        // trigger condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsNotSent($adminEmail);
        $I->seeEmailIsSentTo('custom-admin-mail@test.org', $adminConditionEmail);
    }


    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeMailBehaviourMailTemplateForUser(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $userEmail = $I->haveAEmailDocumentForUser();
        $userConditionEmail = $I->haveAEmailDocumentForUser(['to' => 'custom-user-mail@test.org']);

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
                'type'       => 'mailBehaviour',
                'identifier' => 'mailTemplate',
                'mailType'   => 'copy',
                'value'      => [
                    'en' => [
                        'id'   => $userConditionEmail->getId(),
                        'type' => 'document'
                    ],
                    'de' => [
                        'id'   => null,
                        'type' => null
                    ]
                ]
            ]
        ];

        $testFormBuilder = $this->generateSimpleForm();
        $testFormBuilder->addFormConditionBlock($conditions, $actions);

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, $userEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        // trigger condition
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'text1');

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsNotSent($userEmail);
        $I->seeEmailIsSentTo('custom-user-mail@test.org', $userConditionEmail);
    }
}

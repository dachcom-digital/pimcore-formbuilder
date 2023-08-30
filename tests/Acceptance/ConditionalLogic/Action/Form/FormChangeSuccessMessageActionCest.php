<?php

namespace DachcomBundle\Test\Acceptance\ConditionalLogic\Action\Form;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;

/**
 * Action "changeSuccessMessage"
 */
class FormChangeSuccessMessageActionCest extends AbstractActionCest
{
    public function testElementChangeSuccessMessageToSimpleText(AcceptanceTester $I): void
    {
        $actions = [
            [
                'type'       => 'successMessage',
                'identifier' => 'string',
                'value'      => 'success.message'
            ]
        ];

        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = $this->runTestWithActions($I, $actions, null, false, $adminEmail);

        $this->triggerCondition($I, $testFormBuilder);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText('success.message', 10, '.form-success-wrapper');

    }

    public function testElementChangeSuccessMessageToSnippet(AcceptanceTester $I): void
    {
        $snippet = $I->haveASnippet('mail-success-snippet', ['controller' => 'App\Controller\DefaultController', 'action' => 'snippetAction']);

        $actions = [
            [
                'type'       => 'successMessage',
                'identifier' => 'snippet',
                'value'      => [
                    'en' => [
                        'id'   => $snippet->getId(),
                        'type' => 'document'
                    ],
                    'de' => [
                        'id'   => null,
                        'type' => null
                    ]
                ]
            ]
        ];

        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = $this->runTestWithActions($I, $actions, null, false, $adminEmail);

        $this->triggerCondition($I, $testFormBuilder);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('snippet content with id %s', $snippet->getId()), 5, '.form-success-wrapper h3');
    }

    public function testElementChangeSuccessMessageToSnippetWithAnotherLocale(AcceptanceTester $I): void
    {
        $snippetEn = $I->haveASnippet('mail-success-snippet-en', ['controller' => 'App\Controller\DefaultController', 'action' => 'snippetAction']);
        $snippetDe = $I->haveASnippet('mail-success-snippet-de', ['controller' => 'App\Controller\DefaultController', 'action' => 'snippetAction'], 'de');

        $actions = [
            [
                'type'       => 'successMessage',
                'identifier' => 'snippet',
                'value'      => [
                    'en' => [
                        'id'   => $snippetEn->getId(),
                        'type' => 'document'
                    ],
                    'de' => [
                        'id'   => $snippetDe->getId(),
                        'type' => 'document'
                    ]
                ]
            ]
        ];

        $adminEmail = $I->haveAEmailDocumentForAdmin([], 'de');

        $testFormBuilder = $this->runTestWithActions(
            $I, $actions, null, false, $adminEmail, null, 'de'
        );

        $this->triggerCondition($I, $testFormBuilder);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('snippet content with id %s', $snippetDe->getId()), 5, '.form-success-wrapper h3');
    }

    public function testElementChangeSuccessMessageToDocumentRedirect(AcceptanceTester $I): void
    {
        $redirectDocument = $I->haveAPageDocument('form-test-redirect');

        $actions = [
            [
                'type'         => 'successMessage',
                'identifier'   => 'redirect',
                'flashMessage' => 'thanks.message',
                'value'        => [
                    'en' => [
                        'id'   => $redirectDocument->getId(),
                        'type' => 'document'
                    ],
                    'de' => [
                        'id'   => null,
                        'type' => null
                    ]
                ]
            ]
        ];

        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $testFormBuilder = $this->runTestWithActions($I, $actions, null, false, $adminEmail);

        $this->triggerCondition($I, $testFormBuilder);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', $redirectDocument->getFullPath()), 10, '.form-success-wrapper');
    }

    public function testElementChangeSuccessMessageToDocumentRedirectToAnotherLocale(AcceptanceTester $I): void
    {
        $redirectDocumentEn = $I->haveAPageDocument('form-test-redirect-en');
        $redirectDocumentDe = $I->haveAPageDocument('form-test-redirect-de', [], 'de');

        $actions = [
            [
                'type'         => 'successMessage',
                'identifier'   => 'redirect',
                'flashMessage' => 'thanks.message',
                'value'        => [
                    'en' => [
                        'id'   => $redirectDocumentEn->getId(),
                        'type' => 'document'
                    ],
                    'de' => [
                        'id'   => $redirectDocumentDe->getId(),
                        'type' => 'document'
                    ],
                ]
            ]
        ];

        $adminEmail = $I->haveAEmailDocumentForAdmin([], 'de');
        $testFormBuilder = $this->runTestWithActions(
            $I, $actions, null, false, $adminEmail, null, 'de'
        );

        $this->triggerCondition($I, $testFormBuilder);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', $redirectDocumentDe->getFullPath()), 10, '.form-success-wrapper');
    }

    public function testElementChangeSuccessMessageToExternalRedirect(AcceptanceTester $I): void
    {
        $actions = [
            [
                'type'       => 'successMessage',
                'identifier' => 'redirect_external',
                'value'      => 'http://www.universe.com'
            ]
        ];

        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $testFormBuilder = $this->runTestWithActions($I, $actions, null, false, $adminEmail);

        $this->triggerCondition($I, $testFormBuilder);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', 'http://www.universe.com'), 10, '.form-success-wrapper');
    }
}

<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Action\Form;

use DachcomBundle\Test\acceptance\ConditionalLogic\Condition\AbstractActionCest;
use DachcomBundle\Test\AcceptanceTester;

/**
 * Action "changeSuccessMessage"
 */
class FormChangeSuccessMessageActionCest extends AbstractActionCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeSuccessMessageToSimpleText(AcceptanceTester $I)
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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeSuccessMessageToSnippet(AcceptanceTester $I)
    {
        $snippet = $I->haveASnippet('mail-success-snippet', ['controller' => 'default', 'action' => 'snippet']);

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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeSuccessMessageToSnippetWithAnotherLocale(AcceptanceTester $I)
    {
        $snippetEn = $I->haveASnippet('mail-success-snippet-en', ['controller' => 'default', 'action' => 'snippet']);
        $snippetDe = $I->haveASnippet('mail-success-snippet-de', ['controller' => 'default', 'action' => 'snippet'], 'de');

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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeSuccessMessageToDocumentRedirect(AcceptanceTester $I)
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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeSuccessMessageToDocumentRedirectToAnotherLocale(AcceptanceTester $I)
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

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testElementChangeSuccessMessageToExternalRedirect(AcceptanceTester $I)
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

<?php

namespace DachcomBundle\Test\Acceptance\EmailProperties;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use Pimcore\Model\Document\Snippet;

class MailSuccessFullySentPropertyCest
{
    public function testSuccessFullySentPropertyWithString(AcceptanceTester $I): void
    {
        $mailTemplate = $I->haveAEmailDocumentForAdmin();

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $channels = [
            [
                'type'  => 'email',
                'email' => $mailTemplate
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels, 'form.success.submission');

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, 'bootstrap_4_layout.html.twig', $outputWorkflow);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText('form.success.submission', 10, '.form-success-wrapper');

    }

    public function testSuccessFullySentPropertyWithSnippet(AcceptanceTester $I): void
    {
        $mailTemplate = $I->haveAEmailDocumentForAdmin();

        /** @var Snippet $snippet */
        $snippet = $I->haveASnippet('mail-success-snippet', ['controller' => 'App\Controller\DefaultController', 'action' => 'snippetAction']);
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $channels = [
            [
                'type'  => 'email',
                'email' => $mailTemplate
            ]
        ];

        $successManagement = [
            'type'       => 'successManagement',
            'identifier' => 'snippet',
            'value'      => [
                'default' => [
                    'id'      => $snippet->getId(),
                    'path'    => $snippet->getId(),
                    'type'    => 'document',
                    'subtype' => 'snippet',
                ]
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels, $successManagement);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, 'bootstrap_4_layout.html.twig', $outputWorkflow);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('snippet content with id %s', $snippet->getId()), 5, '.form-success-wrapper h3');
    }

    public function testSuccessFullySentPropertyWithDocument(AcceptanceTester $I): void
    {
        $mailTemplate = $I->haveAEmailDocumentForAdmin();

        $successDocument = $I->haveAPageDocument('mail-success-document');
        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $channels = [
            [
                'type'  => 'email',
                'email' => $mailTemplate
            ]
        ];

        $successManagement = [
            'type'       => 'successManagement',
            'identifier' => 'redirect',
            'value'      => [
                'default' => [
                    'id'      => $successDocument->getId(),
                    'path'    => $successDocument->getId(),
                    'type'    => 'document',
                    'subtype' => 'page',
                ]
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels, $successManagement);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, 'bootstrap_4_layout.html.twig', $outputWorkflow);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', $successDocument->getFullPath()), 5, '.form-success-wrapper');
    }

    public function testSuccessFullySentPropertyWithExternalRedirectUrlAsString(AcceptanceTester $I): void
    {
        $mailTemplate = $I->haveAEmailDocumentForAdmin();

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $channels = [
            [
                'type'  => 'email',
                'email' => $mailTemplate
            ]
        ];

        $successManagement = [
            'type'       => 'successManagement',
            'identifier' => 'redirect_external',
            'value'      => 'https://www.universe.com',
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels, $successManagement);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, 'bootstrap_4_layout.html.twig', $outputWorkflow);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', 'https://www.universe.com'), 5, '.form-success-wrapper');
    }
}

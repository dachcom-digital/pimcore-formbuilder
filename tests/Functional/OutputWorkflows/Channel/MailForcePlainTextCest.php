<?php

namespace DachcomBundle\Test\Functional\OutputWorkflows\Channel;

use DachcomBundle\Test\Support\Helper\Traits;
use DachcomBundle\Test\Support\FunctionalTester;

class MailForcePlainTextCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testPlainTextMailSubmission(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $channels = [
            [
                'type' => 'email',
                'email' => $adminEmail,
                'configuration' => [
                    'forcePlainText' => true
                ]
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'form_div_layout.html.twig', $outputWorkflow);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $searchText = ' **Single\_checkbox:** 1';

        $I->seeEmptyEmailSubmissionType('html', $adminEmail);
        $I->seeInSubmittedEmailBodyOfType($searchText, 'text', $adminEmail);
        $I->dontSeeInSubmittedEmailBodyOfType('<strong>Single_checkbox:</strong>', 'html', $adminEmail);
    }
}

<?php

namespace DachcomBundle\Test\functional\MailSubmissionTypes;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;

class SubmissionTypesCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testHtmlMail(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailSubmissionType('utf-8', 'text', $adminEmail);
        $I->seeEmailSubmissionType('utf-8', 'html', $adminEmail);

        // Html2Text/Html2Text library change text to uppercase if in <strong>, <b>, <td> or <h> tag.
        $searchText = ' **Single\_checkbox:** 1';

        $I->seeInSubmittedEmailBodyOfType($searchText, 'text', $adminEmail);
        $I->seeInSubmittedEmailBodyOfType('<strong>Single_checkbox:</strong>', 'html', $adminEmail);

    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testForcedTextMail(FunctionalTester $I)
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


        // Html2Text/Html2Text library change text to uppercase if in <strong>, <b>, <td> or <h> tag.
        $searchText = ' **Single\_checkbox:** 1';

        $I->seeEmptyEmailSubmissionType('html', $adminEmail);
        $I->seeInSubmittedEmailBodyOfType($searchText, 'text', $adminEmail);
        $I->dontSeeInSubmittedEmailBodyOfType('<strong>Single_checkbox:</strong>', 'html', $adminEmail);
    }
}

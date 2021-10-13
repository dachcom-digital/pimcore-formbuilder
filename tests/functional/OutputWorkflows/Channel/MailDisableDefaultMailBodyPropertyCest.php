<?php

namespace DachcomBundle\Test\functional\OutputWorkflows\Channel;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;

class MailDisableDefaultMailBodyPropertyCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testDisableBodyPropertyAsDisabled(FunctionalTester $I)
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
                    'disableDefaultMailBody' => false
                ]
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'form_div_layout.html.twig', $outputWorkflow);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->cantSeePropertyKeysInEmail($adminEmail, ['_form_builder_disabled_default_mail_body']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testDisableBodyPropertyAsEnabled(FunctionalTester $I)
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
                    'disableDefaultMailBody' => true
                ]
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'form_div_layout.html.twig', $outputWorkflow);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seePropertyKeysInEmail($adminEmail, ['_form_builder_disabled_default_mail_body']);

    }
}

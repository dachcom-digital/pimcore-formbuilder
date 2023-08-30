<?php

namespace DachcomBundle\Test\Functional\OutputWorkflows\Channel;

use DachcomBundle\Test\Support\Helper\Traits;
use DachcomBundle\Test\Support\FunctionalTester;

class MailIgnoreFieldsPropertyCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testIgnoreFieldsForMultipleField(FunctionalTester $I)
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
                    'ignoreFields' => [
                        'simple_text_input_1', 'simple_text_input_4',
                    ]
                ]
            ]
        ];

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, 'form_div_layout.html.twig', $outputWorkflow);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->cantSeePropertyKeysInEmail($adminEmail, ['simple_text_input_1', 'simple_text_input_4']);
    }
}

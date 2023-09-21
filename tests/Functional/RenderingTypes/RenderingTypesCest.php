<?php

namespace DachcomBundle\Test\Functional\RenderingTypes;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Helper\Traits;

class RenderingTypesCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testStaticRenderingTypeOnTwigEngine(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $mainEmail = $I->haveAEmailDocumentForAdmin();
        $secondEmail = $I->haveAEmailDocumentForUser();
        $document = $I->haveAPageDocument('form-test-with-twig-generated-form', ['action' => 'twigRenderAction']);

        $channels = [
            [
                'type' => 'email',
                'email' => $mainEmail
            ],
            [
                'type' => 'email',
                'email' => $secondEmail
            ]
        ];

        $form = $I->haveAForm($testFormBuilder);

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels);

        try {
            $document->setProperty('form_id', 'text', $form->getId(), false, false);
            $document->setProperty('output_workflow_id', 'text', $outputWorkflow->getId(), false, false);
            $document->save();
        } catch (\Exception $e) {
            // fail silently
        }

        $I->amOnPage('/form-test-with-twig-generated-form');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($mainEmail);
        $I->seeEmailIsSent($secondEmail);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testStaticRenderingTypeOnControllerAction(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $mainEmail = $I->haveAEmailDocumentForAdmin();
        $secondEmail = $I->haveAEmailDocumentForUser();
        $document = $I->haveAPageDocument('form-test-with-controller-generated-form', ['action' => 'controllerRenderAction']);

        $channels = [
            [
                'type' => 'email',
                'email' => $mainEmail
            ],
            [
                'type' => 'email',
                'email' => $secondEmail
            ]
        ];

        $form = $I->haveAForm($testFormBuilder);

        $outputWorkflow = $I->haveAOutputWorkflow('Test Output Workflow', $form, $channels);

        try {
            $document->setProperty('form_id', 'text', $form->getId(), false, false);
            $document->setProperty('output_workflow_id', 'text', $outputWorkflow->getId(), false, false);
            $document->save();
        } catch (\Exception $e) {
            // fail silently
        }

        $I->amOnPage('/form-test-with-controller-generated-form');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($mainEmail);
        $I->seeEmailIsSent($secondEmail);
    }
}

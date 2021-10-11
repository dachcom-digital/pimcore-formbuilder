<?php

namespace DachcomBundle\Test\functional\RenderingTypes;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Helper\Traits;

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

        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $userEmail = $I->haveAEmailDocumentForUser();
        $document = $I->haveAPageDocument('form-test-with-twig-generated-form', ['action' => 'twigRenderAction']);

        $form = $I->haveAForm($testFormBuilder);

        try {
            $document->setProperty('form_id', 'text', $form->getId(), false, false);
            $document->setProperty('mail_id', 'text', $adminEmail->getId(), false, false);
            $document->setProperty('mail_copy_id', 'text', $userEmail->getId(), false, false);
            $document->save();
        } catch (\Exception $e) {
            // fail silently
        }

        $I->amOnPage('/form-test-with-twig-generated-form');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);
        $I->seeEmailIsSent($userEmail);

    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testStaticRenderingTypeOnControllerAction(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $userEmail = $I->haveAEmailDocumentForUser();
        $document = $I->haveAPageDocument('form-test-with-controller-generated-form', ['action' => 'controllerRenderAction']);

        $form = $I->haveAForm($testFormBuilder);

        try {
            $document->setProperty('form_id', 'text', $form->getId(), false, false);
            $document->setProperty('mail_id', 'text', $adminEmail->getId(), false, false);
            $document->setProperty('mail_copy_id', 'text', $userEmail->getId(), false, false);
            $document->save();
        } catch (\Exception $e) {
            // fail silently
        }

        $I->amOnPage('/form-test-with-controller-generated-form');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);
        $I->seeEmailIsSent($userEmail);

    }
}

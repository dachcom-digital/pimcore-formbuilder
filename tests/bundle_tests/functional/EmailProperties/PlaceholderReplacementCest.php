<?php

namespace DachcomBundle\Test\functional\EmailProperties;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;

class PlaceholderReplacementCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testSubjectPlaceholder(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['subject' => 'subject with %simple_text_input_1%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);

        $I->seeSentEmailHasPropertyValue($adminEmail, 'subject', 'subject with TEST_FIRST_NAME');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testSubjectCheckboxValuesInPlaceholder(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['subject' => 'subject with %checkboxes%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);

        $I->seeSentEmailHasPropertyValue($adminEmail, 'subject', 'subject with Check 1, Check 3');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testReplyToPlaceholder(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['replyTo' => '%simple_text_input_4%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);

        $I->seeSentEmailHasPropertyValue($adminEmail, 'replyTo', 'test@test.com');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testFromPlaceholder(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['from' => '%simple_text_input_4%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);

        $I->seeSentEmailHasPropertyValue($adminEmail, 'from', 'test@test.com');
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testRecipientPlaceholder(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['to' => '%simple_text_input_4%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);

        $I->seeSentEmailHasPropertyValue($adminEmail, 'to', 'test@test.com');
    }

}

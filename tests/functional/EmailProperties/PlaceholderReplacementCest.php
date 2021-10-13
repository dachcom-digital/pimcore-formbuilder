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
        $I->seeSentEmailHasPropertyValue($adminEmail, 'subject', sprintf('Debug email: [%s] subject with TEST_FIRST_NAME', $adminEmail->getKey()));
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testSubjectPlaceholderWithFieldsetContainer(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['subject' => 'subject with %fieldset_container_sub_text_field_1%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $testFormBuilder->addFormFieldContainer(
            'fieldset',
            'fieldset_container',
            [],
            [
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field_1',
                    'display_name' => 'Text Field 1',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ]
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);
        $I->amOnPage('/form-test');

        $repeaterSelector = $testFormBuilder->getFormFieldSelector(1, 'fieldset_container');
        $subFieldSelector = 'input#formbuilder_1_fieldset_container_0_sub_text_field_1';
        $I->fillField(sprintf('%s %s', $repeaterSelector, $subFieldSelector), 'TEST_FIELDSET_CONTAINER_VALUE');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);
        $I->seeSentEmailHasPropertyValue($adminEmail, 'subject', sprintf('Debug email: [%s] subject with TEST_FIELDSET_CONTAINER_VALUE', $adminEmail->getKey()));
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
        $I->seeSentEmailHasPropertyValue($adminEmail, 'subject', sprintf('Debug email: [%s] subject with Check 1, Check 3', $adminEmail->getKey()));
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
        $I->seeEmailIsSentTo('test@test.com', $adminEmail);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testRecipientPlaceholderWithFieldsetContainer(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin(['to' => '%fieldset_container_sub_text_field_1%']);

        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $testFormBuilder->addFormFieldContainer(
            'fieldset',
            'fieldset_container',
            [],
            [
                [
                    'type'         => 'text',
                    'name'         => 'sub_text_field_1',
                    'display_name' => 'Text Field 1',
                    'constraints'  => [],
                    'options'      => [],
                    'optional'     => [],
                ]
            ]
        );

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);
        $I->amOnPage('/form-test');

        $repeaterSelector = $testFormBuilder->getFormFieldSelector(1, 'fieldset_container');
        $subFieldSelector = 'input#formbuilder_1_fieldset_container_0_sub_text_field_1';
        $I->fillField(sprintf('%s %s', $repeaterSelector, $subFieldSelector), 'test@test.com');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailIsSent($adminEmail);
        $I->seeEmailIsSentTo('test@test.com', $adminEmail);
    }

}

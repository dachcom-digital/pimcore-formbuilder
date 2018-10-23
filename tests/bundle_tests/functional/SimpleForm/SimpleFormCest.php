<?php

namespace DachcomBundle\Test\functional\SimpleForm;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;

class SimpleFormCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleForm(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_0'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_1'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_2'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_3'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_0'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_1'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_2'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'));
        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'single_checkbox'));

        $I->seeElement($testFormBuilder->getFormFieldSelector(1, 'submit'));
        $I->seeElement($testFormBuilder->getFormFieldTokenSelector(1));
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionWithThrownValidationMessages(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_simple_text_input_1"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_simple_text_input_2"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_simple_text_input_4"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_simple_text_area"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[contains(@class, "checkbox-custom")]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[contains(@class, "radio-custom")]//following-sibling::ul//li');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionWithMissingMail(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->see('error while sending mail: mail not sent.', '.message.message-error');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionToAdminWithSuccess(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $userEmail = $I->haveAEmailDocumentForUser();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->see('Success!', '.message.message-success');

        $I->seeEmailIsSent($adminEmail);
        $I->seePropertiesInEmail($adminEmail, [
            'simple_dropdown'       => 'Simple DropDown Value 1<br>',
            'simple_text_input_1'   => 'TEST_FIRST_NAME',
            'simple_text_input_2'   => 'TEST_LAST_NAME',
            'simple_text_input_3'   => '+43 1234 67 89',
            'simple_text_input_4'   => 'test@test.com',
            'simple_text_area'      => 'DUMMY LOREM IPSUM TEXT AREA TEXT',
            'radios'                => 'Radio 3<br>',
            'checkboxes'            => 'Check 3<br>',
            'single_checkbox'       => 1,
            '_form_builder_id'      => 1,
            '_form_builder_is_copy' => 0,
            '_form_builder_preset'  => null
        ]);

        $I->seeEmailIsNotSent($userEmail);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionToAdminAndUserWithSuccess(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($testFormBuilder);
        $adminMail = $I->haveAEmailDocumentForAdmin();
        $userMail = $I->haveAEmailDocumentForUser(['to' => '%email%']);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminMail, $userMail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->see('Success!', '.message.message-success');

        $I->seeEmailIsSent($adminMail);
        $I->seePropertiesInEmail($adminMail, [
            'simple_dropdown'       => 'Simple DropDown Value 1<br>',
            'simple_text_input_1'   => 'TEST_FIRST_NAME',
            'simple_text_input_2'   => 'TEST_LAST_NAME',
            'simple_text_input_3'   => '+43 1234 67 89',
            'simple_text_input_4'   => 'test@test.com',
            'simple_text_area'      => 'DUMMY LOREM IPSUM TEXT AREA TEXT',
            'radios'                => 'Radio 3<br>',
            'checkboxes'            => 'Check 3<br>',
            'single_checkbox'       => 1,
            '_form_builder_id'      => 1,
            '_form_builder_is_copy' => 0,
            '_form_builder_preset'  => null
        ]);

        $I->seeEmailIsSent($userMail);
        $I->seePropertiesInEmail($userMail, [
            'simple_dropdown'       => 'Simple DropDown Value 1<br>',
            'simple_text_input_1'   => 'TEST_FIRST_NAME',
            'simple_text_input_2'   => 'TEST_LAST_NAME',
            'simple_text_input_3'   => '+43 1234 67 89',
            'simple_text_input_4'   => 'test@test.com',
            'simple_text_area'      => 'DUMMY LOREM IPSUM TEXT AREA TEXT',
            'radios'                => 'Radio 3<br>',
            'checkboxes'            => 'Check 3<br>',
            'single_checkbox'       => 1,
            '_form_builder_id'      => 1,
            '_form_builder_is_copy' => 1,
            '_form_builder_preset'  => null
        ]);
    }
}

<?php

namespace DachcomBundle\Test\Functional;

use DachcomBundle\Test\FunctionalTester;

class SimpleFormCest
{
    /**
     * @param FunctionalTester $I
     */
    public function testSimpleForm(FunctionalTester $I)
    {
        $I->haveASimpleForm('dachcom_test');

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement('form[name="formbuilder_1"]');

        $I->seeElement('form select#formbuilder_1_salutation');
        $I->seeElement('form input#formbuilder_1_prename');
        $I->seeElement('form input#formbuilder_1_lastname');
        $I->seeElement('form input#formbuilder_1_phone');
        $I->seeElement('form input#formbuilder_1_email');
        $I->seeElement('form input#formbuilder_1_checkbox_0');
        $I->seeElement('form input#formbuilder_1_checkbox_1');
        $I->seeElement('form input#formbuilder_1_checkbox_2');
        $I->seeElement('form input#formbuilder_1_checkbox_3');
        $I->seeElement('form input#formbuilder_1_radios_0');
        $I->seeElement('form input#formbuilder_1_radios_2');
        $I->seeElement('form input#formbuilder_1_radios_3');
        $I->seeElement('form input#formbuilder_1_radios_3');
        $I->seeElement('form textarea#formbuilder_1_comment');
        $I->seeElement('form input#formbuilder_1__token');
        $I->seeElement('form button#formbuilder_1_send');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionWithThrownValidationMessages(FunctionalTester $I)
    {
        $I->haveASimpleForm('dachcom_test');

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement('form[name="formbuilder_1"]');

        $I->click('form button#formbuilder_1_send');

        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_prename"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_email"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[@for="formbuilder_1_comment"]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[contains(@class, "checkbox-custom")]//following-sibling::ul//li');
        $I->see('This value should not be blank.', '//form//label[contains(@class, "radio-custom")]//following-sibling::ul//li');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionWithMissingMail(FunctionalTester $I)
    {
        $I->haveASimpleForm('dachcom_test');

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillForm($I);

        $I->click('form button#formbuilder_1_send');
        $I->see('error while sending mail: mail not sent.', '.message.message-error');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionToAdminWithSuccess(FunctionalTester $I)
    {
        $I->haveASimpleForm('dachcom_test', true);

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillForm($I);

        $I->click('form button#formbuilder_1_send');
        $I->see('Success!', '.message.message-success');
        $I->seeEmailIsSentToAdmin();
        $I->seeEmailIsNotSentToUser();
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSimpleFormSubmissionToAdminAndUserWithSuccess(FunctionalTester $I)
    {
        $I->haveASimpleForm('dachcom_test', true, true);

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillForm($I);

        $I->click('form button#formbuilder_1_send');
        $I->see('Success!', '.message.message-success');
        $I->seeEmailIsSentToAdmin();
        $I->seeEmailIsSentToUser();
    }

    /**
     * @param FunctionalTester $I
     */
    private function fillForm(FunctionalTester $I)
    {
        $I->fillField('form input#formbuilder_1_prename', 'TEST');
        $I->fillField('form input#formbuilder_1_lastname', 'MAN');
        $I->fillField('form input#formbuilder_1_phone', '123456789');
        $I->fillField('form input#formbuilder_1_email', 'test@test.com');
        $I->fillField('form textarea#formbuilder_1_comment', 'DUMMY TEX');
        $I->selectOption('form select#formbuilder_1_salutation', 'mr');
        $I->selectOption('form input#formbuilder_1_radios_3', 'radio_d');
        $I->checkOption('form input#formbuilder_1_checkbox_3');
    }
}

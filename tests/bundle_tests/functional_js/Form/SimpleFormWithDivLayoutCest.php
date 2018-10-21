<?php

namespace DachcomBundle\Test\Functional;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\AcceptanceTester;

class SimpleFormWithDivLayoutCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testSimpleForm(AcceptanceTester $I)
    {
        $document = $I->haveAPageDocument('form-test', 'javascript');
        $form = $I->haveAForm('dachcom_test', 'simple-javascript');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement('.form-builder-wrapper');
        $I->seeElement('form[name="formbuilder_1"]');

        $this->fillForm($I);

        $I->click('form button#formbuilder_1_send');

        $I->waitForText('Success!', 10, '.form-success-wrapper');
    }
}

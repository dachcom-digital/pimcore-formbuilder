<?php

namespace DachcomBundle\Test\acceptance\Form;

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
        $testFormBuilder = $this->generateSimpleForm(true);

        $form = $I->haveAForm($testFormBuilder);

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->waitForText('Success!', 10, '.form-success-wrapper');
    }
}

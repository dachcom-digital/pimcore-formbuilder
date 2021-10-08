<?php

namespace DachcomBundle\Test\functional\EmailProperties;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;
use Pimcore\Model\Property;

class MailForcePlainTextCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testPlainTextMailSubmission(FunctionalTester $I)
    {
        $property = new Property();
        $property->setName('mail_force_plain_text');
        $property->setType('checkbox');
        $property->setData(true);
        $property->setInheritable(true);
        $property->setInherited(false);

        $adminEmail = $I->haveAEmailDocumentForAdmin(['properties' => [$property]]);
        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailSubmissionType('text/plain', 'text', $adminEmail);
    }
}

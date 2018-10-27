<?php

namespace DachcomBundle\Test\functional\EmailProperties;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;
use Pimcore\Model\Property;

class MailDisableDefaultMailBodyPropertyCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testDisableBodyPropertyAsEnabled(FunctionalTester $I)
    {
        $property = new Property();
        $property->setName('mail_disable_default_mail_body');
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
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->cantSeePropertyKeysInEmail($adminEmail, ['body']);
    }


    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testDisableBodyPropertyAsDisabled(FunctionalTester $I)
    {
        $property = new Property();
        $property->setName('mail_disable_default_mail_body');
        $property->setType('checkbox');
        $property->setData(false);
        $property->setInheritable(true);
        $property->setInherited(false);

        $adminEmail = $I->haveAEmailDocumentForAdmin(['properties' => [$property]]);
        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');
        $I->seeElement($testFormBuilder->getFormSelector(1));

        $this->fillSimpleForm($testFormBuilder, $I);

        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seePropertyKeysInEmail($adminEmail, ['body']);
    }
}

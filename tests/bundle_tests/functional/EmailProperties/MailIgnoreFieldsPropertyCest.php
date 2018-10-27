<?php

namespace DachcomBundle\Test\functional\EmailProperties;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;
use Pimcore\Model\Property;

class MailIgnoreFieldsPropertyCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testIgnoreFieldsForSingleField(FunctionalTester $I)
    {
        $property = new Property();
        $property->setName('mail_ignore_fields');
        $property->setType('text');
        $property->setData('simple_text_input_1');
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

        $I->cantSeePropertyKeysInEmail($adminEmail, ['simple_text_input_1']);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testIgnoreFieldsForMultipleField(FunctionalTester $I)
    {
        $property = new Property();
        $property->setName('mail_ignore_fields');
        $property->setType('text');
        $property->setData('simple_text_input_1,simple_text_input_4');
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

        $I->cantSeePropertyKeysInEmail($adminEmail, ['simple_text_input_1', 'simple_text_input_4']);
    }
}

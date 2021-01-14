<?php

namespace DachcomBundle\Test\functional\MailSubmissionTypes;

use Dachcom\Codeception\Util\VersionHelper;
use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;
use Pimcore\Model\Property;

class SubmissionTypesCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testHtmlMail(FunctionalTester $I)
    {
        $adminEmail = $I->haveAEmailDocumentForAdmin();
        $document = $I->haveAPageDocument('form-test');
        $testFormBuilder = $this->generateSimpleForm();
        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail);

        $I->amOnPage('/form-test');

        $this->fillSimpleForm($testFormBuilder, $I);
        $this->clickSimpleFormSubmit($testFormBuilder, $I);

        $I->seeEmailSubmissionType('text/html', $adminEmail);

        // Html2Text/Html2Text library change text to uppercase if in <strong>, <b>, <td> or <h> tag.
        $searchText = VersionHelper::pimcoreVersionIsGreaterOrEqualThan('6.6.0') ? 'SINGLE_CHECKBOX:' : 'Single_checkbox:';

        $I->seeInSubmittedEmailChildrenBody($searchText, $adminEmail);
        $I->seeInSubmittedEmailBody('<strong>Single_checkbox:</strong>', $adminEmail);
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \Exception
     */
    public function testForcedTextMail(FunctionalTester $I)
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

        $I->seeEmailSubmissionType('text/plain', $adminEmail);
        $I->dontHaveSubmittedEmailChildren($adminEmail);

        // Html2Text/Html2Text library change text to uppercase if in <strong>, <b>, <td> or <h> tag.
        $searchText = VersionHelper::pimcoreVersionIsGreaterOrEqualThan('6.6.0') ? 'SINGLE_CHECKBOX:' : 'Single_checkbox:';

        $I->seeInSubmittedEmailBody($searchText, $adminEmail);
        $I->dontSeeInSubmittedEmailBody('<strong>Single_checkbox:</strong>', $adminEmail);
    }
}

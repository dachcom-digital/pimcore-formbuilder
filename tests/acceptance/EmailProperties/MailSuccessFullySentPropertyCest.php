<?php

namespace DachcomBundle\Test\acceptance\EmailProperties;

use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\Util\TestFormBuilder;
use Pimcore\Model\Property;

class MailSuccessFullySentPropertyCest
{
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testSuccessFullySentPropertyWithString(AcceptanceTester $I)
    {
        $property = new Property();
        $property->setName('mail_successfully_sent');
        $property->setType('text');
        $property->setData('form.success.submission');
        $property->setInheritable(true);
        $property->setInherited(false);

        $mailTemplate = $I->haveAEmailDocumentForAdmin(['properties' => [$property]]);

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText('form.success.submission', 10, '.form-success-wrapper');

    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testSuccessFullySentPropertyWithSnippet(AcceptanceTester $I)
    {
        $snippet = $I->haveASnippet('mail-success-snippet', ['controller' => 'App\Controller\DefaultController', 'action' => 'snippetAction']);

        $property = new Property();
        $property->setName('mail_successfully_sent');
        $property->setType('document');
        $property->setData($snippet->getId());
        $property->setInheritable(true);
        $property->setInherited(false);

        $mailTemplate = $I->haveAEmailDocumentForAdmin(['properties' => [$property]]);

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('snippet content with id %s', $snippet->getId()), 5, '.form-success-wrapper h3');
    }

    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testSuccessFullySentPropertyWithDocument(AcceptanceTester $I)
    {
        $successDocument = $I->haveAPageDocument('mail-success-document');

        $property = new Property();
        $property->setName('mail_successfully_sent');
        $property->setType('document');
        $property->setData($successDocument->getId());
        $property->setInheritable(true);
        $property->setInherited(false);

        $mailTemplate = $I->haveAEmailDocumentForAdmin(['properties' => [$property]]);

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', $successDocument->getFullPath()), 5, '.form-success-wrapper');
    }
    /**
     * @param AcceptanceTester $I
     *
     * @throws \Exception
     */
    public function testSuccessFullySentPropertyWithExternalRedirectUrlAsString(AcceptanceTester $I)
    {
        $property = new Property();
        $property->setName('mail_successfully_sent');
        $property->setType('text');
        $property->setData('http://www.universe.com');
        $property->setInheritable(true);
        $property->setInherited(false);

        $mailTemplate = $I->haveAEmailDocumentForAdmin(['properties' => [$property]]);

        $document = $I->haveAPageDocument('form-test', ['action' => 'javascriptAction']);

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(true)
            ->addFormFieldInput('simple_text_input_1')
            ->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $formTemplate = 'bootstrap_4_layout.html.twig';
        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $mailTemplate, null, $formTemplate);
        $I->amOnPage('/form-test');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->waitForText(sprintf('redirect to: %s', 'http://www.universe.com'), 5, '.form-success-wrapper');
    }
}

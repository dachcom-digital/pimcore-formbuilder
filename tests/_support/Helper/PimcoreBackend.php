<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use DachcomBundle\Test\Util\FileGeneratorHelper;
use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Util\TestFormBuilder;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormInterface;
use Pimcore\File;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Snippet;
use Pimcore\Model\Tool\Email\Log;
use Pimcore\Model\Document\Tag\Areablock;
use Pimcore\Model\Document\Tag\Checkbox;
use Pimcore\Model\Document\Tag\Href;
use Pimcore\Model\Document\Tag\Select;
use Pimcore\Tests\Util\TestHelper;
use Pimcore\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Serializer\Serializer;

class PimcoreBackend extends Module
{
    /**
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        FileGeneratorHelper::preparePaths();
        parent::_before($test);
    }

    /**
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        TestHelper::cleanUp();

        //re-create form data folder.
        try {
            $folder = new Asset\Folder();
            $folder->setParentId(1);
            $folder->setFilename('formdata');
            $folder->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(
                sprintf('[FORMBUILDER ERROR] error while re-creating formdata folder. message was: ' . $e->getMessage())
            );
        }

        FormHelper::removeAllForms();
        FileGeneratorHelper::cleanUp();

        parent::_after($test);
    }

    /**
     * Actor Function to create a Form
     *
     * @param TestFormBuilder $formBuilder
     *
     * @return FormInterface
     */
    public function haveAForm(TestFormBuilder $formBuilder)
    {
        $form = $this->createForm($formBuilder);
        $this->assertInstanceOf(Form::class, $this->getFormManager()->getById($form->getId()));

        return $form;
    }

    /**
     * Actor Function to create a Page Document
     *
     * @param string      $documentKey
     * @param null|string $action
     * @param null|string $controller
     * @param null|string $locale
     *
     * @return Page
     */
    public function haveAPageDocument(
        $documentKey = 'form-test',
        $action = null,
        $controller = null,
        $locale = 'en'
    ) {
        $document = $this->generatePageDocument($documentKey, $action, $controller, $locale);

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document page. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Page::class, Page::getById($document->getId()));

        return $document;
    }

    /**
     * Actor Function to create a Snippet
     *
     * @param string $snippetKey
     * @param array  $elements
     * @param string $locale
     *
     * @return null|Snippet
     */
    public function haveASnippetDocument($snippetKey, $elements = [], $locale = 'en')
    {
        $snippet = $this->generateSnippetDocument($snippetKey, $elements, $locale);

        try {
            $snippet->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document snippet. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Snippet::class, $snippet);

        return $snippet;
    }

    /**
     * Actor Function to create a mail document for admin
     *
     * @param array  $mailParams
     * @param string $locale
     *
     * @return Email
     */
    public function haveAEmailDocumentForAdmin(array $mailParams = [], $locale = 'en')
    {
        return $this->haveAEmailDocumentForType('admin', $mailParams, $locale);
    }

    /**
     * Actor Function to create a mail document for user
     *
     * @param array  $mailParams
     * @param string $locale
     *
     * @return Email
     */
    public function haveAEmailDocumentForUser(array $mailParams = [], $locale = 'en')
    {
        return $this->haveAEmailDocumentForType('user', $mailParams, $locale);
    }

    /**
     * Actor Function to create a mail document for given type
     *
     * @param        $type
     * @param array  $mailParams
     * @param string $locale
     *
     * @return Email
     */
    public function haveAEmailDocumentForType($type, array $mailParams = [], $locale = 'en')
    {
        $emailDocument = $mailTemplate = $this->generateEmailDocument(sprintf('email-%s', $type), $mailParams, $locale);
        $this->assertInstanceOf(Email::class, $emailDocument);

        return $emailDocument;
    }

    /**
     * @param     $fileName
     * @param int $fileSizeInMb Mb
     */
    public function haveFile($fileName, $fileSizeInMb = 1)
    {
        FileGeneratorHelper::generateDummyFile($fileName, $fileSizeInMb);
    }

    /**
     * @param $fileName
     */
    public function seeDownload($fileName)
    {
        $supportDir = FileGeneratorHelper::getDownloadPath();
        $filePath = $supportDir . $fileName;

        $this->assertTrue(is_file($filePath));
    }

    /**
     * Actor Function to place a form area on a document
     *
     * @param Page          $document
     * @param FormInterface $form
     * @param bool          $mailTemplate
     * @param bool          $copyMailTemplate
     * @param string        $formTemplate
     */
    public function seeAFormAreaElementPlacedOnDocument(
        Page $document,
        FormInterface $form,
        $mailTemplate = null,
        $copyMailTemplate = null,
        $formTemplate = 'form_div_layout.html.twig'
    ) {

        if ($mailTemplate !== null) {
            $this->assertInstanceOf(Email::class, $mailTemplate);
        }

        $sendUserCopy = false;
        if ($copyMailTemplate !== null) {
            $sendUserCopy = true;
            $this->assertInstanceOf(Email::class, $copyMailTemplate);
        }

        $document->setElements($this->createFormArea($form->getId(), $formTemplate, $mailTemplate, $sendUserCopy, $copyMailTemplate));

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document. message was: ' . $e->getMessage()));
        }

        $this->assertCount(6, $document->getElements());
    }

    /**
     * Actor Function to see if given email has been sent
     *
     * @param Email $email
     */
    public function seeEmailIsSent(Email $email)
    {
        $this->assertInstanceOf(Email::class, $email);

        $foundEmails = $this->getEmailsFromDocumentIds([$email->getId()]);
        $this->assertEquals(1, count($foundEmails));
    }

    /**
     * Actor Function to see if an email has been sent to admin
     *
     * @param Email $email
     */
    public function seeEmailIsNotSent(Email $email)
    {
        $this->assertInstanceOf(Email::class, $email);

        $foundEmails = $this->getEmailsFromDocumentIds([$email->getId()]);
        $this->assertEquals(0, count($foundEmails));
    }

    /**
     * Actor Function to see if admin email contains given properties
     *
     * @param Email $mail
     * @param array $properties
     */
    public function seePropertiesInEmail(Email $mail, array $properties)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            foreach ($properties as $propertyKey => $propertyValue) {
                $key = array_search($propertyKey, array_column($params, 'key'));
                if ($key === false) {
                    $this->fail(sprintf('Failed asserting that mail params array has the key "%s".', $propertyKey));
                }

                $data = $params[$key];
                $this->assertEquals($propertyValue, $data['data']['value']);
            }
        }
    }

    /**
     * Actor Function to see if admin email contains given properties
     *
     * @param Email $mail
     * @param array $properties
     */
    public function seePropertyKeysInEmail(Email $mail, array $properties)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            foreach ($properties as $propertyKey) {
                $key = array_search($propertyKey, array_column($params, 'key'));
                $this->assertNotSame(false, $key);
            }
        }
    }

    /**
     * Actor Function to see if admin email not contains given properties
     *
     * @param Email $mail
     * @param array $properties
     */
    public function cantSeePropertyKeysInEmail(Email $mail, array $properties)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);
            foreach ($properties as $propertyKey) {
                $this->assertFalse(
                    array_search(
                        $propertyKey,
                        array_column($params, 'key')),
                    sprintf('Failed asserting that search for "%s" is false.', $propertyKey)
                );
            }
        }
    }

    /**
     * @param Email  $mail
     * @param string $string
     */
    public function seeInRenderedEmailBody(Email $mail, string $string)
    {
        $this->assertInstanceOf(Email::class, $mail);

        $foundEmails = $this->getEmailsFromDocumentIds([$mail->getId()]);
        $this->assertGreaterThan(0, count($foundEmails));

        $serializer = $this->getSerializer();

        foreach ($foundEmails as $email) {
            $params = $serializer->decode($email->getParams(), 'json', ['json_decode_associative' => true]);

            $bodyKey = array_search('body', array_column($params, 'key'));
            $this->assertNotSame(false, $bodyKey);

            $data = $params[$bodyKey];
            $this->assertContains($string, $data['data']['value']);
        }
    }

    /**
     * @param FormInterface $form
     * @param string        $fieldName
     *
     * @throws \Exception
     */
    public function seeZipFileInPimcoreAssetsFromField(FormInterface $form, string $fieldName)
    {
        $assetList = Asset::getList([
            'condition' => sprintf(
                'path = "/formdata/%s/" AND filename LIKE "%s-%%"',
                File::getValidFilename($form->getName()), $fieldName
            )
        ]);

        $this->assertEquals(1, count($assetList));

        foreach ($assetList as $asset) {
            $this->assertEquals('application/zip', $asset->getMimeType());
        }
    }

    /**
     * Actor Function to see if a key has been stored in admin translations
     *
     * @param string $key
     *
     */
    public function seeKeyInFrontendTranslations(string $key)
    {
        /** @var Translator $translator */
        $translator = \Pimcore::getContainer()->get('pimcore.translator');
        $this->assertTrue($translator->getCatalogue()->has($key));
    }

    /**
     * @param array $documentIds
     *
     * @return Log[]
     */
    protected function getEmailsFromDocumentIds(array $documentIds)
    {
        $emailLogs = new Log\Listing();
        $emailLogs->addConditionParam(sprintf('documentId IN (%s)', implode(',', $documentIds)));

        return $emailLogs->load();
    }

    /**
     * @param  TestFormBuilder $formBuilder
     *
     * @return FormInterface
     */
    protected function createForm(TestFormBuilder $formBuilder)
    {
        $manager = $this->getFormManager();
        $form = $manager->save($formBuilder->build());

        return $form;
    }

    /**
     * @return FormManager
     */
    protected function getFormManager()
    {
        try {
            $manager = $this->getContainer()->get(FormManager::class);
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while creating form. message was: ' . $e->getMessage()));
            return null;
        }

        return $manager;
    }

    /**
     * API Function to create a Snippet
     *
     * @param        $snippetKey
     * @param array  $elements
     * @param string $locale
     *
     * @return null|Snippet
     */
    protected function generateSnippetDocument($snippetKey, $elements = [], $locale = 'en')
    {
        $document = new Snippet();
        $document->setController('default');
        $document->setAction('snippet');
        $document->setType('snippet');
        $document->setElements($elements);
        $document->setParentId(1);
        $document->setUserOwner(1);
        $document->setUserModification(1);
        $document->setCreationDate(time());
        $document->setKey($snippetKey);
        $document->setProperty('language', 'text', $locale, false, 1);
        $document->setPublished(true);

        return $document;

    }

    /**
     * @param string      $key
     * @param null|string $action
     * @param null|string $controller
     * @param string      $locale
     *
     * @return Page
     */
    protected function generatePageDocument($key = 'form-test', $action = null, $controller = null, $locale = 'en')
    {
        $action = is_null($action) ? 'default' : $action;
        $controller = is_null($controller) ? '@AppBundle\Controller\DefaultController' : $controller;

        $document = TestHelper::createEmptyDocumentPage('', false);
        $document->setController($controller);
        $document->setAction($action);
        $document->setKey($key);
        $document->setProperty('language', 'text', $locale, false, 1);

        return $document;
    }

    /**
     * @param string $key
     * @param array  $params
     * @param string $locale
     *
     * @return null|Email
     */
    protected function generateEmailDocument($key = 'form-test-email', array $params = [], $locale = 'en')
    {
        $documentKey = uniqid(sprintf('%s-', $key));

        $document = new Email();
        $document->setType('email');
        $document->setParentId(1);
        $document->setUserOwner(1);
        $document->setUserModification(1);
        $document->setCreationDate(time());
        $document->setModule('FormBuilderBundle');
        $document->setController('Email');
        $document->setAction('email');
        $document->setTemplate('FormBuilderBundle:Email:email.html.twig');
        $document->setKey($documentKey);
        $document->setProperty('language', 'text', $locale, false, true);
        $document->setProperty('test_identifier', 'text', $documentKey, false, false);

        $to = 'recpient@test.org';
        if (isset($params['to'])) {
            $to = $params['to'];
        }

        $subject = sprintf('FORM EMAIL %s', $documentKey);
        if (isset($params['subject'])) {
            $subject = $params['subject'];
        }

        $document->setTo($to);
        $document->setSubject($subject);

        if (isset($params['replyTo'])) {
            $document->setReplyTo($params['replyTo']);
        }

        if (isset($params['cc'])) {
            $document->setCc($params['cc']);
        }

        if (isset($params['bcc'])) {
            $document->setBcc($params['bcc']);
        }

        if (isset($params['from'])) {
            $document->setFrom($params['from']);
        }

        if (isset($params['properties'])) {
            $document->setProperties($params['properties']);
        }

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while creating email. message was: ' . $e->getMessage()));
            return null;
        }

        return $document;
    }

    /**
     * @param int    $formId
     * @param string $formType
     * @param null   $mailTemplate
     * @param bool   $sendUserCopy
     * @param null   $copyMailTemplate
     *
     * @return array
     */
    protected function createFormArea($formId = 1, $formType = 'form_div_layout.html.twig', $mailTemplate = null, $sendUserCopy = false, $copyMailTemplate = null)
    {
        $blockArea = new Areablock();
        $blockArea->setName(FormHelper::AREA_TEST_NAMESPACE);

        $formNameSelect = new Select();
        $formNameSelect->setName(sprintf('%s:1.formName', FormHelper::AREA_TEST_NAMESPACE));
        $formNameSelect->setDataFromEditmode($formId);

        $formTypeSelect = new Select();
        $formTypeSelect->setName(sprintf('%s:1.formType', FormHelper::AREA_TEST_NAMESPACE));
        $formTypeSelect->setDataFromEditmode($formType);

        $sendMailTemplateHref = new Href();
        $sendMailTemplateHref->setName(sprintf('%s:1.sendMailTemplate', FormHelper::AREA_TEST_NAMESPACE));

        $data = [];
        if ($mailTemplate instanceof Email) {
            $data = [
                'id'      => $mailTemplate->getId(),
                'type'    => 'document',
                'subtype' => $mailTemplate->getType()
            ];
        }

        $sendMailTemplateHref->setDataFromEditmode($data);

        $userCopyCheckbox = new Checkbox();
        $userCopyCheckbox->setName(sprintf('%s:1.userCopy', FormHelper::AREA_TEST_NAMESPACE));
        $userCopyCheckbox->setDataFromEditmode($sendUserCopy);

        $sendCopyMailTemplateHref = new Href();
        $sendCopyMailTemplateHref->setName(sprintf('%s:1.sendCopyMailTemplate', FormHelper::AREA_TEST_NAMESPACE));

        $data = [];
        if ($copyMailTemplate instanceof Email && $sendUserCopy === true) {
            $data = [
                'id'      => $copyMailTemplate->getId(),
                'type'    => 'document',
                'subtype' => $copyMailTemplate->getType()
            ];
        }

        $sendCopyMailTemplateHref->setDataFromEditmode($data);

        $blockArea->setDataFromEditmode([
            [
                'key'    => '1',
                'type'   => 'formbuilder_form',
                'hidden' => false
            ]
        ]);

        return [
            sprintf('%s', FormHelper::AREA_TEST_NAMESPACE)                        => $blockArea,
            sprintf('%s:1.formName', FormHelper::AREA_TEST_NAMESPACE)             => $formNameSelect,
            sprintf('%s:1.formType', FormHelper::AREA_TEST_NAMESPACE)             => $formTypeSelect,
            sprintf('%s:1.sendCopyMailTemplate', FormHelper::AREA_TEST_NAMESPACE) => $sendCopyMailTemplateHref,
            sprintf('%s:1.sendMailTemplate', FormHelper::AREA_TEST_NAMESPACE)     => $sendMailTemplateHref,
            sprintf('%s:1.userCopy', FormHelper::AREA_TEST_NAMESPACE)             => $userCopyCheckbox
        ];

    }

    /**
     * @return Container
     * @throws \Codeception\Exception\ModuleException
     */
    protected function getContainer()
    {
        return $this->getModule('\\' . PimcoreCore::class)->getContainer();
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        $serializer = null;

        try {
            $serializer = $this->getContainer()->get('pimcore_admin.serializer');
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while getting pimcore admin serializer. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Serializer::class, $serializer);

        return $serializer;
    }
}

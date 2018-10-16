<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormInterface;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Tool\Email\Log;
use Pimcore\Model\Document\Tag\Areablock;
use Pimcore\Model\Document\Tag\Checkbox;
use Pimcore\Model\Document\Tag\Href;
use Pimcore\Model\Document\Tag\Select;
use Pimcore\Tests\Util\TestHelper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Serializer\Serializer;

class PimcoreBackend extends Module
{
    /**
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        TestHelper::cleanUp();
        FormHelper::removeAllForms();

        parent::_after($test);
    }

    /**
     * Actor Function to create a Form
     *
     * @param string $formName
     * @param string $type
     *
     * @return FormInterface
     */
    public function haveAForm($formName = 'MOCK_FORM', $type = 'simple')
    {
        $form = $this->createForm($formName, $type);
        $this->assertInstanceOf(Form::class, $this->getFormManager()->getById($form->getId()));

        return $form;
    }

    /**
     * Actor Function to create a Page Document
     *
     * @param string $documentKey
     *
     * @return Page
     */
    public function haveAPageDocument($documentKey = 'form-test')
    {
        $document = $this->generatePageDocument($documentKey);

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Page::class, Page::getById($document->getId()));

        return $document;
    }

    /**
     * Actor Function to create a mail document for admin
     *
     * @param array $mailParams
     *
     * @return Email
     */
    public function haveAEmailDocumentForAdmin(array $mailParams = [])
    {
        return $this->haveAEmailDocumentForType('admin', $mailParams);
    }

    /**
     * Actor Function to create a mail document for user
     *
     * @param array $mailParams
     *
     * @return Email
     */
    public function haveAEmailDocumentForUser(array $mailParams = [])
    {
        return $this->haveAEmailDocumentForType('user', $mailParams);
    }

    /**
     * Actor Function to create a mail document for given type
     *
     * @param       $type
     * @param array $mailParams
     *
     * @return Email
     */
    public function haveAEmailDocumentForType($type, array $mailParams = [])
    {
        $emailDocument = $mailTemplate = $this->generateEmailDocument(sprintf('email-%s', $type), $mailParams);
        $this->assertInstanceOf(Email::class, $emailDocument);

        return $emailDocument;

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
     * Actor Function to see if an email has been sent to admin (specified in form)
     *
     * @param Email $email#
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

        $serializer = null;

        try {
            $serializer = $this->getContainer()->get('pimcore_admin.serializer');
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while getting pimcore admin serializer. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Serializer::class, $serializer);

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
     * @param $formName
     * @param $type
     *
     * @return FormInterface
     */
    protected function createForm($formName, $type = 'simple')
    {
        $manager = $this->getFormManager();

        $data = null;
        switch ($type) {
            case 'simple':
                $data = FormHelper::generateSimpleForm($formName);
                break;
            default:
                $this->fail(sprintf('form creation of type "%s" not possible', $type));
        }

        $form = $manager->save($data);

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
     * @param string $key
     *
     * @return \Pimcore\Model\Document\Page
     */
    protected function generatePageDocument($key = 'form-test')
    {
        $document = TestHelper::createEmptyDocumentPage('', false);
        $document->setController('@AppBundle\Controller\DefaultController');
        $document->setAction('default');
        $document->setKey($key);

        return $document;
    }

    /**
     * @param string $key
     * @param array  $params
     *
     * @return null|Email
     */
    protected function generateEmailDocument($key = 'form-test-email', array $params = [])
    {
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
        $document->setSubject('MOCKED FORM MAIL (' . $key . ')');
        $document->setKey($key);

        if (isset($params['to'])) {
            $document->setTo($params['to']);
        }

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
        return $this->getModule('\\' . PimcoreBundle::class)->getContainer();
    }
}

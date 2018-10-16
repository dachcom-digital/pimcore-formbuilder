<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use DachcomBundle\Test\Util\FormHelper;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\Form;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Tool\Email\Log;
use Pimcore\Model\Document\Tag\Areablock;
use Pimcore\Model\Document\Tag\Checkbox;
use Pimcore\Model\Document\Tag\Href;
use Pimcore\Model\Document\Tag\Select;
use Pimcore\Tests\Util\TestHelper;
use Symfony\Component\DependencyInjection\Container;

class PimcoreBackend extends Module
{
    /**
     * @var array
     */
    protected $generatedEmails = [];

    /**
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        parent::_before($test);

        $this->generatedEmails = [
            'admin' => [],
            'user'  => []
        ];
    }

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
     * @param bool   $addMail
     * @param bool   $addCopyMail
     */
    public function haveASimpleForm($formName = 'MOCK_FORM', $addMail = false, $addCopyMail = false)
    {
        $this->haveASimpleFormOnPage($formName, 'form-test', $addMail, $addCopyMail);
    }

    /**
     * Actor Function to create a Form on a specific Page
     *
     * @param string $formName
     * @param string $documentKey
     * @param bool   $addMail
     * @param bool   $addCopyMail
     */
    public function haveASimpleFormOnPage($formName = 'MOCK_FORM', $documentKey = 'form-test', $addMail = false, $addCopyMail = false)
    {
        $form = $this->createForm($formName);

        $document = $this->generateDocument($documentKey);

        $formId = 1;
        $formType = 'form_div_layout.html.twig';
        $mailTemplate = null;
        $copyMailTemplate = null;
        $sendUserCopy = false;

        if ($addMail === true) {
            $mailTemplate = $this->generateEmail('main-email', 'admin');
        }

        if ($addCopyMail === true) {
            $sendUserCopy = true;
            $copyMailTemplate = $this->generateEmail('main-email-copy', 'user');
        }

        $document->setElements($this->createFormArea($formId, $formType, $mailTemplate, $sendUserCopy, $copyMailTemplate));

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document. message was: ' . $e->getMessage()));
        }

        $this->assertInstanceOf(Form::class, $this->getFormManager()->getById($form->getId()));
        $this->assertInstanceOf(Page::class, Page::getById($document->getId()));

    }

    /**
     *  Actor Function to see if an email has been sent to admin (specified in form)
     */
    public function seeEmailIsSentToAdmin()
    {
        $this->seeEmailIsSentToType('admin');
    }

    /**
     *  Actor Function to see if an email has been sent to admin
     */
    public function seeEmailIsNotSentToAdmin()
    {
        $this->seeEmailIsNotSentToType('admin');
    }

    /**
     *  Actor Function to see if an email has been sent to user (specified in form)
     */
    public function seeEmailIsSentToUser()
    {
        $this->seeEmailIsSentToType('user');
    }

    /**
     *  Actor Function to see if an email has not been sent to user
     */
    public function seeEmailIsNotSentToUser()
    {
        $this->seeEmailIsNotSentToType('user');
    }

    /**
     * @param string $type
     */
    public function seeEmailIsSentToType($type = 'admin')
    {
        $mails = $this->generatedEmails[$type];
        $this->assertGreaterThan(0, count($mails));

        $documentIds = array_map(function (Email $email) {
            return $email->getId();
        }, $mails);

        $foundEmails = $this->getEmailsFromDocumentIds($documentIds);
        $this->assertEquals(count($mails), count($foundEmails));
    }

    /**
     * @param string $type
     */
    public function seeEmailIsNotSentToType($type = 'admin')
    {
        $mails = $this->generatedEmails[$type];

        // to have no mails is valid in this case.
        if (count($mails) === 0) {
            return;
        }

        $documentIds = array_map(function (Email $email) {
            return $email->getId();
        }, $mails);

        $foundEmails = $this->getEmailsFromDocumentIds($documentIds);
        $this->assertEquals(0, count($foundEmails));
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
     *
     * @return \FormBuilderBundle\Storage\FormInterface|null
     */
    protected function createForm($formName)
    {
        $manager = $this->getFormManager();
        $form = $manager->save(FormHelper::generateSimpleForm($formName));

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
    protected function generateDocument($key = 'form-test')
    {
        $document = TestHelper::createEmptyDocumentPage('', false);
        $document->setController('@AppBundle\Controller\DefaultController');
        $document->setAction('default');
        $document->setKey($key);

        return $document;
    }

    /**
     * @param string $key
     * @param string $type
     *
     * @return null|Email
     */
    protected function generateEmail($key = 'form-test-email', $type = 'admin')
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

        try {
            $document->save();
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while creating email. message was: ' . $e->getMessage()));
            return null;
        }

        $this->generatedEmails[$type][] = $document;

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

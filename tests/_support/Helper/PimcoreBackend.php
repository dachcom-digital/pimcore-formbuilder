<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Exception\ModuleException;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Dachcom\Codeception\Util\EditableHelper;
use Dachcom\Codeception\Util\VersionHelper;
use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Util\TestFormBuilder;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Pimcore\File;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;

class PimcoreBackend extends \Dachcom\Codeception\Helper\PimcoreBackend
{
    /**
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        //re-create form data folder.
        try {
            $folder = new Asset\Folder();
            $folder->setParentId(1);
            $folder->setFilename('formdata');
            $folder->save();
        } catch (\Exception $e) {
            Debug::debug(
                sprintf('[FORMBUILDER ERROR] error while re-creating formdata folder. message was: ' . $e->getMessage())
            );
        }

        FormHelper::removeAllForms();

        parent::_after($test);
    }

    /**
     * Actor Function to create a Form
     *
     * @param TestFormBuilder $formBuilder
     *
     * @return FormDefinitionInterface
     *
     * @throws ModuleException
     */
    public function haveAForm(TestFormBuilder $formBuilder)
    {
        $formDefinition = $this->createForm($formBuilder);
        $this->assertInstanceOf(FormDefinition::class, $this->getFormManager()->getById($formDefinition->getId()));

        return $formDefinition;
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
    public function haveAEmailDocumentForType($type, array $mailParams = [], $locale = null)
    {
        $params = array_merge([
            'module'     => 'FormBuilderBundle',
            'controller' => 'Email',
            'action'     => 'email',
            'template'   => 'FormBuilderBundle:Email:email.html.twig'
        ], $mailParams);

        $document = $mailTemplate = $this->generateEmailDocument(sprintf('email-%s', $type), $params, $locale);

        try {
            $document->save();
        } catch (\Exception $e) {
            Debug::debug(sprintf('[TEST BUNDLE ERROR] error while creating email. message was: ' . $e->getMessage()));
            return null;
        }

        $this->assertInstanceOf(Email::class, Email::getById($document->getId()));

        return $document;
    }

    /**
     * Actor Function to place a form area on a document
     *
     * @param Page                    $document
     * @param FormDefinitionInterface $form
     * @param bool                    $mailTemplate
     * @param bool                    $copyMailTemplate
     * @param string                  $formTemplate
     */
    public function seeAFormAreaElementPlacedOnDocument(
        Page $document,
        FormDefinitionInterface $form,
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

        $editables = [
            'formName'             => [
                'type'             => 'select',
                'dataFromEditmode' => $form->getId(),
            ],
            'formType'             => [
                'type'             => 'select',
                'dataFromEditmode' => $formTemplate,
            ],
            'formPreset'           => [
                'type'             => 'select',
                'dataFromEditmode' => 'custom',
            ],
            'outputWorkflow'       => [
                'type'             => 'select',
                'dataFromEditmode' => 'none',
            ],
            'userCopy'             => [
                'type'             => 'checkbox',
                'dataFromEditmode' => $sendUserCopy,
            ],
            'sendMailTemplate'     => [
                'type'             => 'relation',
                'dataFromEditmode' => $mailTemplate instanceof Email ? [
                    'id'      => $mailTemplate->getId(),
                    'type'    => 'document',
                    'subtype' => $mailTemplate->getType()
                ] : [],
            ],
            'sendCopyMailTemplate' => [
                'type'             => 'relation',
                'dataFromEditmode' => $copyMailTemplate instanceof Email ? [
                    'id'      => $copyMailTemplate->getId(),
                    'type'    => 'document',
                    'subtype' => $copyMailTemplate->getType()
                ] : [],
            ],
        ];

        try {
            $editables = EditableHelper::generateEditablesForArea('formbuilder_form', $editables);
        } catch (\Throwable $e) {
            throw new ModuleException($this, sprintf('area generator error: %s', $e->getMessage()));
        }

        if (VersionHelper::pimcoreVersionIsGreaterOrEqualThan('6.8.0')) {
            $document->setEditables($editables);
        } else {
            $document->setElements($editables);
        }

        if (method_exists($document, 'setMissingRequiredEditable')) {
            $document->setMissingRequiredEditable(false);
        }

        try {
            $document->save();
        } catch (\Exception $e) {
            Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document. message was: ' . $e->getMessage()));
        }

        $this->assertCount(8, VersionHelper::pimcoreVersionIsGreaterOrEqualThan('6.8.0') ? $document->getEditables() : $document->getElements());

        \Pimcore::collectGarbage();
        //\Pimcore\Cache\Runtime::set(sprintf('document_%s', $document->getId()), null);

    }

    /**
     * @param FormDefinitionInterface $form
     * @param string                  $fieldName
     *
     * @throws \Exception
     */
    public function seeZipFileInPimcoreAssetsFromField(FormDefinitionInterface $form, string $fieldName)
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
     * @param FormDefinitionInterface $form
     * @param string                  $fieldName
     *
     * @throws \Exception
     */
    public function cantSeeZipFileInPimcoreAssetsFromField(FormDefinitionInterface $form, string $fieldName)
    {
        $assetList = Asset::getList([
            'condition' => sprintf(
                'path = "/formdata/%s/" AND filename LIKE "%s-%%"',
                File::getValidFilename($form->getName()), $fieldName
            )
        ]);

        $this->assertEquals(0, count($assetList));

    }

    /**
     * @param TestFormBuilder $formBuilder
     *
     * @return FormDefinitionInterface
     * @throws \Exception
     */
    protected function createForm(TestFormBuilder $formBuilder)
    {
        $manager = $this->getFormManager();

        return $manager->save($formBuilder->build());
    }

    /**
     * @return FormDefinitionManager
     */
    protected function getFormManager()
    {
        try {
            $manager = $this->getContainer()->get(FormDefinitionManager::class);
        } catch (\Exception $e) {
            Debug::debug(sprintf('[FORMBUILDER ERROR] error while creating form. message was: ' . $e->getMessage()));
            return null;
        }

        return $manager;
    }
}

<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Exception\ModuleException;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Dachcom\Codeception\Util\EditableHelper;
use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Util\TestFormBuilder;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\OutputWorkflowManager;
use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Pimcore\File;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Email;
use Pimcore\Model\Document\Page;

class PimcoreBackend extends \Dachcom\Codeception\Helper\PimcoreBackend
{
    public function _after(TestInterface $test)
    {
        parent::_after($test);

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
    }

    /**
     * Actor Function to create a Form
     */
    public function haveAForm(TestFormBuilder $formBuilder): FormDefinitionInterface
    {
        $formDefinition = $this->createForm($formBuilder);
        $this->assertInstanceOf(FormDefinition::class, $this->getFormManager()->getById($formDefinition->getId()));

        return $formDefinition;
    }

    /**
     * Actor Function to update a Form
     */
    public function updateAForm(FormDefinitionInterface $form, TestFormBuilder $formBuilder): FormDefinitionInterface
    {
        return $this->updateForm($form, $formBuilder);
    }

    /**
     * Actor Function to create an Output Workflow
     */
    public function haveAOutputWorkflow(
        string $outputWorkflowName,
        FormDefinitionInterface $form,
        array $channelDefinitions,
        string $successMessage = 'Success!'
    ): OutputWorkflowInterface {
        $outputWorkflow = $this->createOutputWorkflow($outputWorkflowName, $form, $channelDefinitions, $successMessage);

        $this->assertInstanceOf(OutputWorkflowInterface::class, $this->getOutputWorkflowManager()->getById($outputWorkflow->getId()));

        return $outputWorkflow;
    }

    /**
     * Actor Function to create a mail document for admin
     */
    public function haveAEmailDocumentForAdmin(array $mailParams = [], string $locale = 'en'): Email
    {
        return $this->haveAEmailDocumentForType('admin', $mailParams, $locale);
    }

    /**
     * Actor Function to create a mail document for user
     */
    public function haveAEmailDocumentForUser(array $mailParams = [], string $locale = 'en'): Email
    {
        return $this->haveAEmailDocumentForType('user', $mailParams, $locale);
    }

    /**
     * Actor Function to create a mail document for given type
     */
    public function haveAEmailDocumentForType(string $type, array $mailParams = [], ?string $locale = null): Email
    {
        $params = array_merge([
            'controller' => 'FormBuilderBundle\Controller\EmailController',
            'action'     => 'emailAction',
            'template'   => 'FormBuilderBundle:Email:email.html.twig'
        ], $mailParams);

        $document = $this->generateEmailDocument(sprintf('email-%s', $type), $params, $locale);

        try {
            $document->save();
        } catch (\Exception $e) {
            Debug::debug(sprintf('[TEST BUNDLE ERROR] error while creating email. message was: %s', $e->getMessage()));
        }

        $this->assertInstanceOf(Email::class, Email::getById($document->getId()));

        return $document;
    }

    /**
     * Actor Function to place a form area on a document
     */
    public function seeAFormAreaElementPlacedOnDocument(
        Page $document,
        FormDefinitionInterface $form,
        ?Email $mailTemplate = null,
        ?Email $additionalMailTemplate = null,
        ?string $formTemplate = 'form_div_layout.html.twig',
        ?OutputWorkflowInterface $outputWorkflow = null
    ): void {

        $outputWorkflowChannels = [];

        if ($mailTemplate !== null) {
            $this->assertInstanceOf(Email::class, $mailTemplate);
            $outputWorkflowChannels = [
                [
                    'type'  => 'email',
                    'email' => $mailTemplate
                ]
            ];
        }

        if ($additionalMailTemplate !== null) {
            $this->assertInstanceOf(Email::class, $additionalMailTemplate);
            $outputWorkflowChannels[] =
                [
                    'type'  => 'email',
                    'email' => $additionalMailTemplate
                ];
        }

        if ($mailTemplate !== null && !$outputWorkflow instanceof OutputWorkflowInterface) {
            $outputWorkflow = $this->createOutputWorkflow('Test Output Workflow', $form, $outputWorkflowChannels);
        }

        $editables = [
            'formName'       => [
                'type'             => 'select',
                'dataFromEditmode' => $form->getId(),
            ],
            'formType'       => [
                'type'             => 'select',
                'dataFromEditmode' => $formTemplate,
            ],
            'formPreset'     => [
                'type'             => 'select',
                'dataFromEditmode' => 'custom',
            ],
            'outputWorkflow' => [
                'type'             => 'select',
                'dataFromEditmode' => $outputWorkflow->getId(),
            ],
        ];

        try {
            $editables = EditableHelper::generateEditablesForArea('formbuilder_form', $editables);
        } catch (\Throwable $e) {
            throw new ModuleException($this, sprintf('area generator error: %s', $e->getMessage()));
        }

        $document->setEditables($editables);
        $document->setMissingRequiredEditable(false);

        try {
            $document->save();
        } catch (\Exception $e) {
            Debug::debug(sprintf('[FORMBUILDER ERROR] error while saving document. message was: %s', $e->getMessage()));
        }

        $this->assertCount(5, $document->getEditables());

        \Pimcore::collectGarbage();
        //\Pimcore\Cache\Runtime::set(sprintf('document_%s', $document->getId()), null);
    }

    /**
     * Actor Function to see if a form attachment field has been stored as pimcore asset
     */
    public function seeZipFileInPimcoreAssetsFromField(FormDefinitionInterface $form, string $fieldName): void
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
     * Actor Function to not see if a form attachment field has been stored as pimcore asset
     */
    public function cantSeeZipFileInPimcoreAssetsFromField(FormDefinitionInterface $form, string $fieldName): void
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
     * API Method to create a form
     */
    protected function createForm(TestFormBuilder $formBuilder): FormDefinitionInterface
    {
        $manager = $this->getFormManager();

        return $manager->save($formBuilder->build());
    }

    /**
     * API Method to update a form
     */
    protected function updateForm(FormDefinitionInterface $form, TestFormBuilder $formBuilder): FormDefinitionInterface
    {
        $manager = $this->getFormManager();

        return $manager->save($formBuilder->build(), $form->getId());
    }

    /**
     * API Method to create an output workflow
     */
    protected function createOutputWorkflow(
        string $name,
        FormDefinitionInterface $form,
        array $channelDefinitions,
        string $successMessage = 'Success!',
    ): OutputWorkflowInterface {
        $manager = $this->getOutputWorkflowManager();

        $outputWorkflow = $manager->save([
            'name'           => $name,
            'formDefinition' => $form,
        ]);

        foreach ($channelDefinitions as $channelDefinition) {

            $channel = new OutputWorkflowChannel();
            $channel->setType($channelDefinition['type']);
            $channel->setOutputWorkflow($outputWorkflow);

            if ($channelDefinition['type'] === 'email') {
                /** @var Email $email */
                $email = $channelDefinition['email'];
                $emailConfiguration = $channelDefinition['configuration'] ?? [];
                $channel->setConfiguration([
                    'default' => [
                        'mailTemplate'           => [
                            'id'      => $email->getId(),
                            'path'    => $email->getFullPath(),
                            'type'    => 'document',
                            'subtype' => 'email',
                        ],
                        'ignoreFields'           => $emailConfiguration['ignoreFields'] ?? null,
                        'allowAttachments'       => $emailConfiguration['allowAttachments'] ?? true,
                        'forcePlainText'         => $emailConfiguration['forcePlainText'] ?? false,
                        'disableDefaultMailBody' => $emailConfiguration['disableDefaultMailBody'] ?? false,
                        'mailLayoutData'         => $emailConfiguration['mailLayoutData'] ?? null,
                    ]
                ]);

            } elseif ($channelDefinition['type'] === 'object') {
                // @todo!
            }

            $outputWorkflow->addChannel($channel);
        }

        $outputWorkflow->setSuccessManagement([
            'type'       => 'successManagement',
            'identifier' => 'string',
            'value'      => $successMessage,
        ]);

        $manager->saveRawEntity($outputWorkflow);

        return $outputWorkflow;
    }

    protected function getFormManager(): FormDefinitionManager
    {
        $manager = null;

        try {
            $manager = $this->getContainer()->get(FormDefinitionManager::class);
        } catch (\Exception $e) {
            Debug::debug(sprintf('[FORMBUILDER ERROR] error while loading form manager. message was: %s', $e->getMessage()));
        }

        return $manager;
    }

    protected function getOutputWorkflowManager(): OutputWorkflowManager
    {
        $manager = null;

        try {
            $manager = $this->getContainer()->get(OutputWorkflowManager::class);
        } catch (\Exception $e) {
            Debug::debug(sprintf('[FORMBUILDER ERROR] error while loading output workflow manager. message was: %s', $e->getMessage()));
        }

        return $manager;
    }
}

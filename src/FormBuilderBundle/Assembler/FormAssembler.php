<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FormAssembler
{
    /**
     * @var FormOptionsResolver
     */
    protected $optionsResolver = null;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FormDefinitionManager
     */
    protected $formDefinitionManager;

    /**
     * @var FrontendFormBuilder
     */
    protected $frontendFormBuilder;

    /**
     * @var string
     */
    protected $preset = '';

    /**
     * @param SessionInterface      $session
     * @param FrontendFormBuilder   $frontendFormBuilder
     * @param FormDefinitionManager $formDefinitionManager
     */
    public function __construct(
        SessionInterface $session,
        FrontendFormBuilder $frontendFormBuilder,
        FormDefinitionManager $formDefinitionManager
    ) {
        $this->session = $session;
        $this->frontendFormBuilder = $frontendFormBuilder;
        $this->formDefinitionManager = $formDefinitionManager;
    }

    /**
     * @param FormOptionsResolver $optionsResolver
     */
    public function setFormOptionsResolver(FormOptionsResolver $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function assembleViewVars()
    {
        if (is_null($this->optionsResolver)) {
            throw new \Exception('no valid options resolver found.');
        }

        $builderError = false;
        $exceptionMessage = null;

        $formId = $this->optionsResolver->getFormId();
        if (!empty($formId)) {
            try {
                $formDefinition = $this->formDefinitionManager->getById($formId);
                if (!$formDefinition instanceof FormDefinitionInterface || !$this->formDefinitionManager->configurationFileExists($formId)) {
                    $errorMessage = [];
                    if (!$formDefinition instanceof FormDefinitionInterface) {
                        $errorMessage[] = sprintf('Form with id "%s" is not valid.', $formId);
                    }

                    if (!$this->formDefinitionManager->configurationFileExists($formId)) {
                        $formConfigurationPath = $this->formDefinitionManager->getConfigurationPath($formId);
                        $errorMessage[] = sprintf('Configuration file is not available. This file needs to be generated as "%s".', $formConfigurationPath);
                    }

                    $exceptionMessage = join(' ', $errorMessage);
                    $builderError = true;
                }
            } catch (\Exception $e) {
                $exceptionMessage = $e->getMessage();
                $builderError = true;
            }
        } else {
            $exceptionMessage = 'No valid form selected.';
            $builderError = true;
        }

        $viewVars = [];
        $viewVars['form_layout'] = $this->optionsResolver->getFormLayout();

        if ($builderError === true) {
            $viewVars['message'] = $exceptionMessage;
            $viewVars['form_template'] = null;

            return $viewVars;
        }

        $userOptions = [
            'form_preset'   => $this->optionsResolver->getFormPreset(),
            'form_template' => $this->optionsResolver->getFormTemplateName()
        ];

        /** @var FormInterface $form */
        $form = $this->frontendFormBuilder->buildForm($this->optionsResolver->getFormId(), $userOptions);

        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');

        //store current configuration for further events.
        $sessionBag->set('form_configuration_' . $this->optionsResolver->getFormId(), [
            'form_runtime_options' => [
                'form_preset'    => $this->optionsResolver->getFormPreset(),
                'form_template'  => $this->optionsResolver->getFormTemplateName(),
                'custom_options' => $this->optionsResolver->getCustomOptions()
            ],
            'email'        => [
                'send_copy'             => $this->optionsResolver->getSendCopy(),
                'mail_template_id'      => $this->optionsResolver->getMailTemplateId(),
                'copy_mail_template_id' => $this->optionsResolver->getCopyMailTemplateId()
            ]
        ]);

        $viewVars['form_block_template'] = $this->optionsResolver->getFormBlockTemplate();
        $viewVars['form_template'] = $this->optionsResolver->getFormTemplate();
        $viewVars['form_id'] = $this->optionsResolver->getFormId();
        $viewVars['form_preset'] = $this->optionsResolver->getFormPreset();
        $viewVars['main_layout'] = $this->optionsResolver->getMainLayout();
        $viewVars['form'] = $form->createView();

        return $viewVars;
    }
}

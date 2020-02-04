<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Form\Builder;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\FormInterface;
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
     * @var FormManager
     */
    protected $formManager;

    /**
     * @var Builder
     */
    protected $formBuilder;

    /**
     * @var string
     */
    protected $preset = '';

    /**
     * @param SessionInterface $session
     * @param Builder          $formBuilder
     * @param FormManager      $formManager
     */
    public function __construct(
        SessionInterface $session,
        Builder $formBuilder,
        FormManager $formManager
    ) {
        $this->session = $session;
        $this->formBuilder = $formBuilder;
        $this->formManager = $formManager;
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
                $formData = $this->formManager->getById($formId);
                if (!$formData instanceof FormInterface || !$this->formManager->configurationFileExists($formId)) {
                    $errorMessage = [];
                    if (!$formData instanceof FormInterface) {
                        $errorMessage[] = sprintf('Form with id "%s" is not valid.', $formId);
                    }

                    if (!$this->formManager->configurationFileExists($formId)) {
                        $formConfigurationPath = $this->formManager->getConfigurationPath($formId);
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

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formBuilder->buildForm($this->optionsResolver->getFormId(), $userOptions);

        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');

        //store current configuration for further events.
        $sessionBag->set('form_configuration_' . $this->optionsResolver->getFormId(), [
            'user_options' => [
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

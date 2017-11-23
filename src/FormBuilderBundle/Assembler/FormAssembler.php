<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Form\Builder;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Storage\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FormAssembler
{
    /**
     * @var FormOptionsResolver
     */
    protected $optionsResolver = NULL;

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
     * Form constructor.
     *
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

    public function assembleViewVars()
    {
        if (is_null($this->optionsResolver)) {
            throw new \Exception('no valid options resolver found.');
        }

        $builderError = FALSE;
        $exceptionMessage = NULL;

        $formId = $this->optionsResolver->getFormId();
        if (!empty($formId)) {
            try {
                $formData = $this->formManager->getById($formId);
                if (!$formData instanceof Form) {
                    $exceptionMessage = 'Form (' . $formId . ') is not a valid FormBuilder Element.';
                    $builderError = TRUE;
                }
            } catch (\Exception $e) {
                $exceptionMessage = $e->getMessage();
                $builderError = TRUE;
            }
        } else {
            $exceptionMessage = 'No valid form selected.';
            $builderError = TRUE;
        }

        $viewVars['form_layout'] = $this->optionsResolver->getFormLayout();

        if($builderError === FALSE) {

            $userOptions = [
                'form_preset' => $this->optionsResolver->getFormPreset(),
                'form_template' => $this->optionsResolver->getFormTemplateName()
            ];

            /** @var \Symfony\Component\Form\Form $form */
            $form = $this->formBuilder->buildForm($this->optionsResolver->getFormId(), $userOptions);

            /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
            $sessionBag = $this->session->getBag('form_builder_session');

            //store current configuration for further events.
            $sessionBag->set('form_configuration_' . $this->optionsResolver->getFormId(), [
                'user_options' => [
                    'form_preset' => $this->optionsResolver->getFormPreset(),
                    'form_template' => $this->optionsResolver->getFormTemplateName()
                ],
                'email'       => [
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

        } else {
            $viewVars['message'] = $exceptionMessage;
            $viewVars['form_template'] = NULL;
        }

        return $viewVars;
    }

}
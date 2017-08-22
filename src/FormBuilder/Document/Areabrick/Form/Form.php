<?php

namespace FormBuilderBundle\Document\Areabrick\Form;

use FormBuilderBundle\Form\Builder;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Manager\PresetManager;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;
use Pimcore\Translation\Translator;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\Session\Session;

class Form extends AbstractTemplateAreabrick
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var FormManager
     */
    protected $formManager;

    /**
     * @var PresetManager
     */
    protected $presetManager;

    /**
     * @var Builder
     */
    protected $formBuilder;

    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Form constructor.
     *
     * @param Session         $session
     * @param FormManager     $formManager
     * @param PresetManager   $presetManager
     * @param Builder         $formBuilder
     * @param TemplateManager $templateManager
     * @param Translator      $translator
     */
    public function __construct(
        Session $session,
        FormManager $formManager,
        PresetManager $presetManager,
        Builder $formBuilder,
        TemplateManager $templateManager,
        Translator $translator
    ) {
        $this->session = $session;
        $this->formManager = $formManager;
        $this->presetManager = $presetManager;
        $this->formBuilder = $formBuilder;
        $this->templateManager = $templateManager;
        $this->translator = $translator;
    }

    /**
     * @param Info $info
     */
    public function action(Info $info)
    {
        $view = $info->getView();
        $viewVars = [];

        $formPresetSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formPreset');
        $formTemplateSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formType');

        if ($view->get('editmode') === TRUE) {

            $mains = $this->formManager->getAll();
            $formPresets = $this->presetManager->getAll();

            $formPresetsStore = [];
            $formPresetsInfo = [];
            $availableForms = [];

            if (!empty($mains)) {
                /** @var \FormBuilderBundle\Storage\Form $form */
                foreach ($mains as $form) {
                    $availableForms[] = [$form->getId(), $form->getName()];
                }
            }

            $viewVars['formStore'] = $availableForms;

            $formTemplateStore = [];
            foreach ($this->templateManager->getFormTemplates(TRUE) as $template) {
                $template[1] = $this->translator->trans($template[1], [], 'admin');
                $formTemplateStore[] = $template;
            }

            $viewVars['formTemplateStore'] = $formTemplateStore;

            if ($formTemplateSelection->isEmpty()) {
                $formTemplateSelection->setDataFromResource($this->templateManager->getDefaultFormTemplate());
            }

            if (!empty($formPresets)) {
                $formPresetsStore[] = ['custom', $this->view->trans('form_builder.area.no_form_preset', [], 'admin')];

                foreach ($formPresets as $presetName => $preset) {
                    $formPresetsStore[] = [$presetName, $preset['niceName']];
                    $formPresetsInfo[] = $this->presetManager->getDataForPreview($presetName, $preset);
                }

                if ($formPresetSelection->isEmpty()) {
                    $formPresetSelection->setDataFromResource('custom');
                }

                $viewVars['formPresetStore'] = $formPresetsStore;
                $viewVars['formPresetsInfo'] = $formPresetsInfo;
            }
        }

        $formData = NULL;
        $formId = NULL;
        $formHtml = NULL;
        $messageHtml = NULL;

        $noteMessage = '';
        $noteError = FALSE;

        $sendCopy = $this->getDocumentTag($info->getDocument(), 'checkbox', 'userCopy')->getData() === TRUE;
        $formPreset = $formPresetSelection->getData();

        if (empty($formPreset) || is_null($formPreset)) {
            $formPreset = 'custom';
        }

        $formNameElement = $this->getDocumentTag($info->getDocument(), 'select', 'formName');
        if (!$formNameElement->isEmpty()) {
            $formId = $formNameElement->getData();
        }

        $copyMailTemplate = NULL;

        if (!empty($formId)) {
            try {
                $formData = $this->formManager->getById($formId);

                if (!$formData instanceof \FormBuilderBundle\Storage\Form) {
                    $noteMessage = 'Form (' . $formId . ') is not a valid FormBuilder Element.';
                    $noteError = TRUE;
                }
            } catch (\Exception $e) {
                $noteMessage = $e->getMessage();
                $noteError = TRUE;
            }
        } else {
            $noteMessage = 'No valid form selected.';
            $noteError = TRUE;
        }

        if ($noteError === TRUE) {

            $viewVars = array_merge(
                $viewVars,
                [
                    'form_template' => NULL,
                    'form'          => NULL,
                    'messages'      => NULL,
                    'formId'        => NULL,
                    'formPreset'    => NULL,
                    'notifications' => [
                        'error'   => $noteError,
                        'message' => $noteMessage
                    ],
                ]
            );

            foreach ($viewVars as $var => $varValue) {
                $view->{$var} = $varValue;
            }

            return FALSE;
        }

        $mailTemplate = $this->getDocumentTag($info->getDocument(), 'href', 'sendMailTemplate')->getElement();
        $copyMailTemplate = $this->getDocumentTag($info->getDocument(), 'href', 'sendCopyMailTemplate')->getElement();

        $mailTemplateId = NULL;
        $copyMailTemplateId = NULL;

        if ($mailTemplate instanceof Document\Email) {
            $mailTemplateId = $mailTemplate->getId();
        }

        if ($sendCopy === TRUE && $copyMailTemplate instanceof Document\Email) {
            $copyMailTemplateId = $copyMailTemplate->getId();
        } else { //disable copy!
            $sendCopy = FALSE;
        }

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formBuilder->buildForm($formId);

        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');

        //store current configuration for further events.
        $sessionBag->set('form_configuration_' . $formId, [
            'form_preset' => $formPreset,
            'email'       => [
                'send_copy'             => $sendCopy,
                'mail_template_id'      => $mailTemplateId,
                'copy_mail_template_id' => $copyMailTemplateId
            ]
        ]);

        $formTemplate = $formTemplateSelection->getValue();

        if (empty($formTemplate)) {
            $formTemplate = 'form_div_layout.html.twig';
        }

        $viewVars['form_template'] = '@FormBuilder/Form/Theme/' . $formTemplate;
        $viewVars['form_block_template'] = '@FormBuilder/Form/Theme/Macro/' . $formTemplate;
        $viewVars['form'] = $form->createView();

        $viewVars = array_merge(
            $viewVars,
            [
                'messages'      => $messageHtml,
                'formId'        => $formId,
                'formPreset'    => $formPreset,
                'notifications' => [],
            ]
        );

        foreach ($viewVars as $var => $varValue) {
            $view->{$var} = $varValue;
        }
    }

    /**
     * @return bool
     */
    public function hasEditTemplate()
    {
        return TRUE;
    }

    /**
     * @return string
     */
    public function getViewTemplate()
    {
        return 'FormBuilderBundle:Areas/Form:view.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getEditTemplate()
    {
        return 'FormBuilderBundle:Areas/Form:edit.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Form';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagOpen(Info $info)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagClose(Info $info)
    {
        return '';
    }
}
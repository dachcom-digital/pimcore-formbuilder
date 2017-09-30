<?php

namespace FormBuilderBundle\Document\Areabrick\Form;

use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormManager;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Manager\PresetManager;
use FormBuilderBundle\Assembler\FormAssembler;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;
use Pimcore\Translation\Translator;

class Form extends AbstractTemplateAreabrick
{
    /**
     * @var FormManager
     */
    protected $formManager;

    /**
     * @var PresetManager
     */
    protected $presetManager;

    /**
     * @var FormAssembler
     */
    protected $formAssembler;

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
     * @param FormManager      $formManager
     * @param PresetManager    $presetManager
     * @param FormAssembler          $formAssembler
     * @param TemplateManager  $templateManager
     * @param Translator       $translator
     */
    public function __construct(
        FormManager $formManager,
        PresetManager $presetManager,
        FormAssembler $formAssembler,
        TemplateManager $templateManager,
        Translator $translator
    ) {
        $this->formManager = $formManager;
        $this->presetManager = $presetManager;
        $this->formAssembler = $formAssembler;
        $this->templateManager = $templateManager;
        $this->translator = $translator;
    }

    /**
     * @param Info $info
     */
    public function action(Info $info)
    {
        $view = $info->getView();
        $editViewVars = [];

        $formPresetSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formPreset');
        $formTemplateSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formType');

        if ($view->get('editmode') === TRUE) {

            $mains = $this->formManager->getAll();
            $formPresets = $this->presetManager->getAll($info->getDocument());

            $formPresetsStore = [];
            $formPresetsInfo = [];
            $availableForms = [];

            if (!empty($mains)) {
                /** @var \FormBuilderBundle\Storage\Form $form */
                foreach ($mains as $form) {
                    $availableForms[] = [$form->getId(), $form->getName()];
                }
            }

            $editViewVars['formStore'] = $availableForms;

            $formTemplateStore = [];
            foreach ($this->templateManager->getFormTemplates(TRUE) as $template) {
                $template[1] = $this->translator->trans($template[1], [], 'admin');
                $formTemplateStore[] = $template;
            }

            $editViewVars['formTemplateStore'] = $formTemplateStore;

            if ($formTemplateSelection->isEmpty()) {
                $formTemplateSelection->setDataFromResource($this->templateManager->getDefaultFormTemplate());
            }

            if (!empty($formPresets)) {
                $formPresetsStore[] = ['custom', $this->translator->trans('form_builder.area.no_form_preset', [], 'admin')];

                foreach ($formPresets as $presetName => $preset) {
                    $formPresetsStore[] = [$presetName, $preset['nice_name']];
                    $formPresetsInfo[] = $this->presetManager->getDataForPreview($presetName, $preset);
                }

                if ($formPresetSelection->isEmpty()) {
                    $formPresetSelection->setDataFromResource('custom');
                }

                $editViewVars['formPresetStore'] = $formPresetsStore;
                $editViewVars['formPresetsInfo'] = $formPresetsInfo;
            }
        }

        $formId = NULL;
        $formTemplate = $formTemplateSelection->getValue();
        $sendCopy = $this->getDocumentTag($info->getDocument(), 'checkbox', 'userCopy')->getData() === TRUE;
        $formPreset = $formPresetSelection->getData();

        $formNameElement = $this->getDocumentTag($info->getDocument(), 'select', 'formName');
        if (!$formNameElement->isEmpty()) {
            $formId = $formNameElement->getData();
        }

        $mailTemplate = $this->getDocumentTag($info->getDocument(), 'href', 'sendMailTemplate')->getElement();
        $copyMailTemplate = $this->getDocumentTag($info->getDocument(), 'href', 'sendCopyMailTemplate')->getElement();

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($formId);
        $optionBuilder->setFormTemplate($formTemplate);
        $optionBuilder->setSendCopy($sendCopy);
        $optionBuilder->setMailTemplate($mailTemplate);
        $optionBuilder->setCopyMailTemplate($copyMailTemplate);
        $optionBuilder->setFormPreset($formPreset);

        $this->formAssembler->setFormOptionsResolver($optionBuilder);
        $assemblerViewVars = $this->formAssembler->assembleViewVars();

        foreach (array_merge($editViewVars, $assemblerViewVars) as $var => $varValue) {
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
        return 'FormBuilderBundle:Form:form.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getEditTemplate()
    {
        return 'FormBuilderBundle:Areas/form:edit.' . $this->getTemplateSuffix();
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

<?php

namespace FormBuilderBundle\Document\Areabrick\Form;

use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Assembler\FormAssembler;
use Pimcore\Extension\Document\Areabrick\AbstractAreabrick;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\Response;

class Form extends AbstractAreabrick implements EditableDialogBoxInterface
{
    public function __construct(
        protected FormDialogBuilder $formDialogBuilder,
        protected FormAssembler $formAssembler
    ) {
    }

    public function action(Document\Editable\Area\Info $info): ?Response
    {
        $formId = null;
        $isEditMode = $info->getEditable()?->getEditmode() ?? false;

        /** @var Document\Editable\Select $formPresetSelection */
        $formPresetSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formPreset');
        /** @var Document\Editable\Select $formTemplateSelection */
        $formTemplateSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formType');
        /** @var Document\Editable\Select $outputWorkflowSelection */
        $outputWorkflowSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'outputWorkflow');
        /** @var Document\Editable\Select $formNameElement */
        $formNameElement = $this->getDocumentEditable($info->getDocument(), 'select', 'formName');

        if (!$formNameElement->isEmpty()) {
            $formId = (int) $formNameElement->getData();
        }

        // edit mode variable is not available if there is an edit window
        $info->setParam('form_builder_is_admin_mode', $isEditMode === true);

        $formTemplate = $formTemplateSelection->getValue();
        $formPreset = $formPresetSelection->getData();
        $formOutputWorkflow = $outputWorkflowSelection->isEmpty() || $outputWorkflowSelection->getData() === 'none' ? null : (int) $outputWorkflowSelection->getData();

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($formId);
        $optionBuilder->setFormTemplate($formTemplate);
        $optionBuilder->setFormPreset($formPreset);
        $optionBuilder->setOutputWorkflow($formOutputWorkflow);

        $assemblerViewVars = $this->formAssembler->assemble($optionBuilder);

        foreach ($assemblerViewVars as $var => $varValue) {
            $info->setParam($var, $varValue);
        }

        return null;
    }

    public function getEditableDialogBoxConfiguration(Document\Editable $area, ?Document\Editable\Area\Info $info): EditableDialogBoxConfiguration
    {
        $formSelector = null;
        $outputWorkflowSelector = null;

        if ($info instanceof Document\Editable\Area\Info) {
            /** @var Document\Editable\Select $formNameElement */
            $formSelector = $this->getDocumentEditable($info->getDocument(), 'select', 'formName');
            /** @var Document\Editable\Select $outputWorkflowSelection */
            $outputWorkflowSelector = $this->getDocumentEditable($info->getDocument(), 'select', 'outputWorkflow');
        }

        return $this->formDialogBuilder->build([
            'document'                          => $area->getDocument(),
            'form_selector_editable'            => $formSelector,
            'output_workflow_selector_editable' => $outputWorkflowSelector,
        ]);
    }

    public function getName(): string
    {
        return 'Form';
    }

    public function getHtmlTagOpen(Document\Editable\Area\Info $info): string
    {
        return '';
    }

    public function getHtmlTagClose(Document\Editable\Area\Info $info): string
    {
        return '';
    }

    public function getIcon(): string
    {
        return '/bundles/formbuilder/img/application_form.svg';
    }

    public function getTemplate(): string
    {
        return sprintf('@FormBuilder/form/form.%s', $this->getTemplateSuffix());
    }

    public function getTemplateLocation(): string
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }

    public function getTemplateSuffix(): string
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }
}

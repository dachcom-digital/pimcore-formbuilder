<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;

class FormFieldWidget implements MailEditorWidgetInterface, MailEditorFieldDataWidgetInterface
{
    public function getWidgetGroupName(): string
    {
        return 'form_builder.mail_editor.widget_provider.form_fields';
    }

    public function getSubTypeByField(array $field): string
    {
        return $field['name'];
    }

    public function getWidgetIdentifierByField(string $widgetType, array $field): string
    {
        return sprintf('%s_%s', $widgetType, $field['type']);
    }

    public function getWidgetLabelByField(array $field): string
    {
        return $field['display_name'];
    }

    public function getWidgetConfigByField(array $field): array
    {
        return [
            'show_label' => [
                'type'         => 'checkbox',
                'defaultValue' => true,
                'label'        => 'form_builder.mail_editor.widget_provider.form_fields.show_labels'
            ],
        ];
    }

    public function getValueForOutput(array $config): string
    {
        $renderLabels = !isset($config['show_label']) || $config['show_label'] === true;

        $outputData = $config['outputData'] ?? null;
        $fieldType = $outputData['field_type'] ?? null;

        if (!is_array($outputData)) {
            return '';
        }

        $fieldValue = '';
        if ($fieldType === FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER) {
            $fieldValue .= $this->parseContainerField($outputData, $renderLabels);
        } else {
            $fieldValue .= $this->parseSimpleField($outputData, $renderLabels);
        }

        return $fieldValue;
    }

    protected function parseContainerField(array $outputData, bool $renderLabels): string
    {
        $fieldValue = '';

        $label = $outputData['label'];
        $blockLabel = $outputData['block_label'];

        if (!is_array($outputData['fields'])) {
            return $fieldValue;
        }

        if ($renderLabels === true) {
            $fieldValue .= !empty($label) ? sprintf('%s:<br>', $label) : '';
        }

        foreach ($outputData['fields'] as $blockIndex => $subFieldCollection) {
            $fieldValue .= !empty($blockLabel) ? sprintf('%s:<br>', $blockLabel) : '';
            foreach ($subFieldCollection as $subFieldOutputData) {
                $subFieldType = $subFieldOutputData['field_type'];
                if ($subFieldType === FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER) {
                    $fieldValue .= $this->parseContainerField($subFieldOutputData, $renderLabels);
                } else {
                    // currently we need to force subfields labels in container
                    // since we have no options to define their states.
                    $fieldValue .= $this->parseSimpleField($subFieldOutputData, true);
                    $fieldValue .= '<br>';
                }
            }

            if ($blockIndex + 1 !== count($outputData['fields'])) {
                $fieldValue .= '<br>';
            }
        }

        return $fieldValue;
    }

    protected function parseSimpleField(array $outputData, bool $renderLabels): string
    {
        $fieldValue = '';

        $label = $outputData['label'];
        if ($renderLabels === true) {
            $fieldValue .= !empty($label) ? sprintf('%s: ', $label) : '';
        }

        $fieldValue .= $this->parseFieldValue($outputData['value']);

        return $fieldValue;
    }

    protected function parseFieldValue(array|string $fieldValue): string
    {
        if (is_array($fieldValue)) {
            return implode(', ', $fieldValue);
        }

        return $fieldValue;
    }

    public function getWidgetLabel(): string
    {
        throw new \RuntimeException('"getWidgetLabel" is not allowed within implemented MailEditorFieldDataWidgetInterface');
    }

    public function getWidgetConfig(): array
    {
        throw new \RuntimeException('"getWidgetConfig" is not allowed within implemented MailEditorFieldDataWidgetInterface');
    }
}

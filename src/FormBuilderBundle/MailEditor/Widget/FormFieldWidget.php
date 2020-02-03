<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;

class FormFieldWidget implements MailEditorWidgetInterface, MailEditorFieldDataWidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetGroupName()
    {
        return 'form_builder.mail_editor.widget_provider.form_fields';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubTypeByField(array $field)
    {
        return $field['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetIdentifierByField(string $widgetType, array $field)
    {
        return sprintf('%s_%s', $widgetType, $field['type']);
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetLabelByField(array $field)
    {
        return $field['display_name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetConfigByField(array $field)
    {
        return [
            'show_label' => [
                'type'         => 'checkbox',
                'defaultValue' => true,
                'label'        => 'form_builder.mail_editor.widget_provider.form_fields.show_labels'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueForOutput(array $config)
    {
        $renderLabels = !isset($config['show_label']) || $config['show_label'] === true;

        $outputData = $config['outputData'];
        $fieldType = isset($outputData['field_type']) ? $outputData['field_type'] : null;

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

    /**
     * @param array $outputData
     * @param bool  $renderLabels
     *
     * @return string
     */
    protected function parseContainerField(array $outputData, bool $renderLabels)
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

    /**
     * @param array $outputData
     * @param bool  $renderLabels
     *
     * @return string
     */
    protected function parseSimpleField(array $outputData, bool $renderLabels)
    {
        $fieldValue = '';

        $label = $outputData['label'];
        if ($renderLabels === true) {
            $fieldValue .= !empty($label) ? sprintf('%s: ', $label) : '';
        }

        $fieldValue .= $this->parseFieldValue($outputData['value']);

        return $fieldValue;
    }

    /**
     * @param array|string $fieldValue
     *
     * @return string
     */
    protected function parseFieldValue($fieldValue)
    {
        if (is_array($fieldValue)) {
            return join(', ', $fieldValue);
        }

        return $fieldValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetLabel()
    {
        throw new \RuntimeException('"getWidgetLabel" is not allowed within implemented MailEditorFieldDataWidgetInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetConfig()
    {
        throw new \RuntimeException('"getWidgetConfig" is not allowed within implemented MailEditorFieldDataWidgetInterface');
    }
}

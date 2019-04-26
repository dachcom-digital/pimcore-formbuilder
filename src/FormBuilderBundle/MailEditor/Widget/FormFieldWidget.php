<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;

class FormFieldWidget implements MailEditorWidgetInterface, MailEditorFieldDataWidgetInterface
{
    /**
     * {@inheritDoc}
     */
    public function getWidgetGroupName()
    {
        return 'form_builder.mail_editor.widget_provider.form_fields';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgetIdentifierByField(array $field)
    {
        return $field['name'];
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgetLabelByField(array $field)
    {
        return $field['display_name'];
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgetConfigByField(array $field)
    {
        return [
            'identifier' => $field['name']
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getValueForOutput(array $config)
    {
        $outputData = $config['outputData'];
        $fieldType = $outputData['field_type'];

        if (!is_array($outputData)) {
            return '';
        }

        $fieldValue = '';
        if ($fieldType === FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER) {
            $fieldValue .= $this->parseContainerField($outputData);
        } else {
            $fieldValue .= $this->parseSimpleField($outputData);
        }

        return $fieldValue;
    }

    /**
     * @param array $outputData
     *
     * @return string
     */
    protected function parseContainerField(array $outputData)
    {
        $fieldValue = '';

        $label = $outputData['label'];
        $blockLabel = $outputData['block_label'];

        if (!is_array($outputData['fields'])) {
            return $fieldValue;
        }

        $fieldValue .= !empty($label) ? sprintf('%s:<br>', $label) : '';

        foreach ($outputData['fields'] as $blockIndex => $subFieldCollection) {
            $fieldValue .= !empty($blockLabel) ? sprintf('%s:<br>', $blockLabel) : '';
            foreach ($subFieldCollection as $subFieldOutputData) {
                $subFieldType = $subFieldOutputData['field_type'];
                if ($subFieldType === FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER) {
                    $fieldValue .= $this->parseContainerField($subFieldOutputData);
                } else {
                    $fieldValue .= $this->parseSimpleField($subFieldOutputData);
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
     *
     * @return string
     */
    protected function parseSimpleField(array $outputData)
    {
        $fieldValue = '';

        $label = $outputData['label'];
        $fieldValue .= !empty($label) ? sprintf('%s: ', $label) : '';
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
     * {@inheritDoc}
     */
    public function getWidgetLabel()
    {
        throw new \RuntimeException('"getWidgetLabel" is not allowed within implemented MailEditorFieldDataWidgetInterface');
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgetConfig()
    {
        throw new \RuntimeException('"getWidgetConfig" is not allowed within implemented MailEditorFieldDataWidgetInterface');
    }

}

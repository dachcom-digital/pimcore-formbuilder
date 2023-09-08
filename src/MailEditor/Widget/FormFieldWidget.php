<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\MailEditor\AttributeBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormFieldWidget implements MailEditorWidgetInterface, MailEditorFieldDataWidgetInterface
{
    private const RENDER_TYPE_LABEL = 'L';

    public function __construct(protected TranslatorInterface $translator)
    {
    }

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
        $type = $field['type'] === 'container' ? sprintf('%s_%s', $field['type'], $field['sub_type']) : $field['type'];

        return sprintf('%s_%s', $widgetType, $type);
    }

    public function getWidgetLabelByField(array $field): string
    {
        return $field['display_name'];
    }

    public function getWidgetConfigByField(array $field): array
    {
        $fieldType = $field['type'] ?? null;
        $subType = $field['sub_type'] ?? null;

        $configData = [];

        if ($fieldType === 'container' && $subType === 'repeater') {
            $configData['block_label'] = [
                'type'         => 'input',
                'defaultValue' => '',
                'label'        => 'form_builder.mail_editor.widget_provider.form_fields.repeater_block_label'
            ];
        }

        return $configData;
    }

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string
    {
        $renderType = $attributeBag->get('render_type');

        $outputData = $attributeBag->get('output_data', []);
        $fieldType = $outputData['field_type'] ?? null;

        if (!is_array($outputData)) {
            return '';
        }

        if ($fieldType === FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER) {
            return $this->buildContainerHead($layoutType, $outputData, $attributeBag);
        }

        return $this->parseSimpleField($outputData, $renderType, $layoutType);
    }

    protected function parseSimpleField(array $outputData, string $renderType, string $layoutType): string
    {
        $fieldValue = '';
        $label = $outputData['label'] ?? null;

        if ($renderType === self::RENDER_TYPE_LABEL) {
            return !empty($label)
                ? $this->buildFieldLabel($layoutType, $label)
                : '';
        }

        $fieldValue .= $this->parseFieldValue($outputData['value']);

        return $fieldValue;
    }

    protected function parseFieldValue(array|string $fieldValue): string
    {
        if (is_array($fieldValue)) {
            return implode(', ', $fieldValue);
        }

        return nl2br($fieldValue);
    }

    protected function buildContainerHead(string $layoutType, array $outputData, AttributeBag $attributeBag): string
    {
        if ($outputData['type'] !== 'repeater') {
            return '';
        }

        if ($attributeBag->get('block_label') === null) {
            return '';
        }

        $blockLabel = $this->translator->trans($attributeBag->get('block_label'));

        if ($layoutType === 'html') {
            return sprintf('
                    <table>
                        <tbody>
                            <tr>
                                <td class="block-label"><strong>%s</strong></td>
                            </tr>
                        </tbody>
                    </table>',
                $blockLabel
            );
        }

        return sprintf('%s<br>', $blockLabel);
    }

    protected function buildFieldLabel(string $layoutType, string $label): string
    {
        if ($layoutType === 'html') {
            return $label;
        }

        return sprintf('%s: ', $label);
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

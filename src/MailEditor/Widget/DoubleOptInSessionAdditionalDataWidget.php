<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\MailEditor\AttributeBag;
use FormBuilderBundle\MailEditor\Widget\MailEditorWidgetInterface;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;

class DoubleOptInSessionAdditionalDataWidget implements MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string
    {
        return 'form_builder.mail_editor.widget_provider.double_opt_in_session';
    }

    public function getWidgetLabel(): string
    {
        return 'form_builder.mail_editor.widget_provider.double_opt_in_session.additional_data';
    }

    public function getWidgetConfig(): array
    {
        return [
            'field' => [
                'type'         => 'input',
                'defaultValue' => null,
                'label'        => 'form_builder.mail_editor.widget_provider.double_opt_in_session.additional_data_field'
            ],
        ];
    }

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string
    {
        $rawOutputData = $attributeBag->get('raw_output_data', []);

        if (!array_key_exists('double_opt_in_session', $rawOutputData)) {
            return '[NO VALUE]';
        }

        $doubleOptInSession = $rawOutputData['double_opt_in_session'];
        if (!is_array($doubleOptInSession)) {
            return '[NO VALUE]';
        }

        $field = $attributeBag->get('field', null);
        $additionalData = $doubleOptInSession['additional_data'];

        if (!array_key_exists($field, $additionalData)) {
            return '[NO VALUE]';
        }

        return (string) $additionalData[$field];
    }
}

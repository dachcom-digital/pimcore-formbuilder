<?php

namespace FormBuilderBundle\MailEditor\Widget;

class DateWidget implements MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string
    {
        return 'form_builder.mail_editor.widget_provider.others';
    }

    public function getWidgetLabel(): string
    {
        return 'form_builder.mail_editor.widget_provider.date.date';
    }

    public function getWidgetConfig(): array
    {
        return [
            'format' => [
                'type'         => 'input',
                'defaultValue' => null,
                'label'        => 'form_builder.mail_editor.widget_provider.date.date_format'
            ],
        ];
    }

    public function getValueForOutput(array $config): string
    {
        $format = $config['format'] ?? 'm/d/y H:i:s';

        return date($format);
    }
}

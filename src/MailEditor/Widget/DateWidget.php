<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\MailEditor\AttributeBag;

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

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string
    {
        $format = $attributeBag->get('format', 'm/d/y H:i:s');

        return date($format);
    }
}

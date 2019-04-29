<?php

namespace FormBuilderBundle\MailEditor\Widget;

class DateWidget implements MailEditorWidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetGroupName()
    {
        return 'form_builder.mail_editor.widget_provider.others';
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetLabel()
    {
        return 'form_builder.mail_editor.widget_provider.date.date';
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetConfig()
    {
        return [
            'format' => [
                'type'         => 'input',
                'defaultValue' => null,
                'label'        => 'form_builder.mail_editor.widget_provider.date.date_format'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValueForOutput(array $config)
    {
        $format = isset($config['format']) ? $config['format'] : 'm/d/y H:i:s';

        return date($format);
    }
}

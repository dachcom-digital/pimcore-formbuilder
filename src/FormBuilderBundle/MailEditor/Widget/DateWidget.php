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
        return 'Date';
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
                'label'        => 'Date Format'
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

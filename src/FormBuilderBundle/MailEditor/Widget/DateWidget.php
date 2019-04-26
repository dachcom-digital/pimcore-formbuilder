<?php

namespace FormBuilderBundle\MailEditor\Widget;

class DateWidget implements MailEditorWidgetInterface
{
    public function getWidgetGroupName()
    {
        return 'form_builder.mail_editor.widget_provider.others';
    }

    public function getWidgetLabel()
    {
        return 'Date';
    }

    public function getWidgetConfig()
    {
        return [];
    }

    public function getValueForOutput(array $config)
    {
        $format = isset($config['format']) ? $config['format'] : 'm/d/y H:i:s';

        return date($format);
    }
}

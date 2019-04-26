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
        return date('d.m.Y');
    }
}

<?php

namespace FormBuilderBundle\MailEditor\Widget;

interface MailEditorWidgetInterface
{
    /**
     * @return string
     */
    public function getWidgetGroupName();

    /**
     * @return string
     */
    public function getWidgetLabel();

    /**
     * @return array
     */
    public function getWidgetConfig();

    /**
     * @param array $config
     *
     * @return string
     */
    public function getValueForOutput(array $config);
}

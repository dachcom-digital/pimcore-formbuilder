<?php

namespace FormBuilderBundle\MailEditor\Widget;

interface MailEditorFieldDataWidgetInterface
{
    /**
     * @param array $field
     *
     * @return string
     */
    public function getSubTypeByField(array $field);

    /**
     * @param string $widgetType
     * @param array $field
     *
     * @return string
     */
    public function getWidgetIdentifierByField(string $widgetType, array $field);

    /**
     * @param array $field
     *
     * @return string
     */
    public function getWidgetLabelByField(array $field);

    /**
     * @param array $field
     *
     * @return array
     */
    public function getWidgetConfigByField(array $field);
}

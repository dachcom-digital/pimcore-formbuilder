<?php

namespace FormBuilderBundle\MailEditor\Widget;

interface MailEditorFieldDataWidgetInterface
{
    /**
     * @param array $field
     *
     * @return string
     */
    public function getWidgetIdentifierByField(array $field);

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

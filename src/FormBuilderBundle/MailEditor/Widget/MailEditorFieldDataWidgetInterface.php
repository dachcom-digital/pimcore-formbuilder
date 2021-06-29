<?php

namespace FormBuilderBundle\MailEditor\Widget;

interface MailEditorFieldDataWidgetInterface
{
    public function getSubTypeByField(array $field): string;

    public function getWidgetIdentifierByField(string $widgetType, array $field): string;

    public function getWidgetLabelByField(array $field): string;

    public function getWidgetConfigByField(array $field): array;
}

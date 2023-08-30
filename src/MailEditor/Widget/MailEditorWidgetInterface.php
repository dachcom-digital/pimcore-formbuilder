<?php

namespace FormBuilderBundle\MailEditor\Widget;

interface MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string;

    public function getWidgetLabel(): string;

    public function getWidgetConfig(): array;

    public function getValueForOutput(array $config): string;
}

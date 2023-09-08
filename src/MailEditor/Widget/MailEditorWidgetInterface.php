<?php

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\MailEditor\AttributeBag;

interface MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string;

    public function getWidgetLabel(): string;

    public function getWidgetConfig(): array;

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string;
}

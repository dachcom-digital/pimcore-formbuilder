<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\MailEditor\Widget;

use FormBuilderBundle\MailEditor\AttributeBag;

class DateWidget implements MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string
    {
        return 'form_builder.mail_editor.widget_provider.others';
    }

    public function getWidgetLabel(): string
    {
        return 'form_builder.mail_editor.widget_provider.date.date';
    }

    public function getWidgetConfig(): array
    {
        return [
            'format' => [
                'type'         => 'input',
                'defaultValue' => null,
                'label'        => 'form_builder.mail_editor.widget_provider.date.date_format'
            ],
        ];
    }

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string
    {
        $format = $attributeBag->get('format', 'm/d/y H:i:s');

        return date($format);
    }
}

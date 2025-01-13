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

class DoubleOptInSessionAdditionalDataWidget implements MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string
    {
        return 'form_builder.mail_editor.widget_provider.double_opt_in_session';
    }

    public function getWidgetLabel(): string
    {
        return 'form_builder.mail_editor.widget_provider.double_opt_in_session.additional_data';
    }

    public function getWidgetConfig(): array
    {
        return [
            'field' => [
                'type'         => 'input',
                'defaultValue' => null,
                'label'        => 'form_builder.mail_editor.widget_provider.double_opt_in_session.additional_data_field'
            ],
        ];
    }

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string
    {
        $rawOutputData = $attributeBag->get('raw_output_data', []);

        if (!array_key_exists('double_opt_in_session', $rawOutputData)) {
            return '[NO VALUE]';
        }

        $doubleOptInSession = $rawOutputData['double_opt_in_session'];
        if (!is_array($doubleOptInSession)) {
            return '[NO VALUE]';
        }

        $field = $attributeBag->get('field', null);
        $additionalData = $doubleOptInSession['additional_data'];

        if (!array_key_exists($field, $additionalData)) {
            return '[NO VALUE]';
        }

        return (string) $additionalData[$field];
    }
}

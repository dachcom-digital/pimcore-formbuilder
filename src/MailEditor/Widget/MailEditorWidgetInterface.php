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

interface MailEditorWidgetInterface
{
    public function getWidgetGroupName(): string;

    public function getWidgetLabel(): string;

    public function getWidgetConfig(): array;

    public function getValueForOutput(AttributeBag $attributeBag, string $layoutType): string;
}

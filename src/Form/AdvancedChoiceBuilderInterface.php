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

namespace FormBuilderBundle\Form;

interface AdvancedChoiceBuilderInterface extends ChoiceBuilderInterface
{
    public function getChoiceValue(mixed $element = null): mixed;

    public function getChoiceLabel(mixed $choiceValue, string $key, mixed $value): mixed;

    public function getChoiceAttributes(mixed $element, string $key, mixed $value): mixed;

    public function getGroupBy(mixed $element, string $key, mixed $value): mixed;

    public function getPreferredChoices(mixed $element, string $key, mixed $value): mixed;
}

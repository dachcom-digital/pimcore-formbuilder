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

use Symfony\Component\Form\FormInterface;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
interface FormValuesOutputApplierInterface
{
    public const FIELD_TYPE_SIMPLE = 'simple';
    public const FIELD_TYPE_CONTAINER = 'container';

    public function applyForChannel(FormInterface $form, array $ignoreFields, string $channel, ?string $locale): array;
}

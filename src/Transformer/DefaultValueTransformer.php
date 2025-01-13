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

namespace FormBuilderBundle\Transformer;

class DefaultValueTransformer implements OptionsTransformerInterface
{
    public function transform(mixed $values, ?array $optionConfig = null): mixed
    {
        if (!isset($optionConfig['default_value'])) {
            return $values;
        }

        if (empty($values)) {
            return $optionConfig['default_value'];
        }

        return $values;
    }

    public function reverseTransform(mixed $values, ?array $optionConfig = null): mixed
    {
        if (!isset($optionConfig['default_value'])) {
            return $values;
        }

        if ($values === $optionConfig['default_value']) {
            return '';
        }

        return $values;
    }
}

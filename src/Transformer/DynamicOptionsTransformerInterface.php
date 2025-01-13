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

interface DynamicOptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     */
    public function transform(mixed $rawData, mixed $transformedData, ?array $optionConfig = null): mixed;

    /**
     * Transform symfony choices array into valid ExtJs Array.
     */
    public function reverseTransform(mixed $rawData, mixed $transformedData, ?array $optionConfig = null): mixed;
}

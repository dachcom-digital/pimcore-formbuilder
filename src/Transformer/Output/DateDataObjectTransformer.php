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

namespace FormBuilderBundle\Transformer\Output;

use Carbon\Carbon;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Input\InputTransformerInterface;
use Symfony\Component\Form\FormInterface;

class DateDataObjectTransformer implements OutputTransformerInterface, InputTransformerInterface
{
    public function getValueReverse(FieldDefinitionInterface $fieldDefinition, mixed $formValue): ?\DateTime
    {
        if (!in_array($fieldDefinition->getType(), ['date', 'date_time', 'time', 'birthday'])) {
            return $formValue;
        }

        if (!is_string($formValue)) {
            return $formValue;
        }

        return Carbon::parse($formValue)->toDateTime();
    }

    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed
    {
        if (!$rawValue instanceof \DateTime) {
            return null;
        }

        $carbon = Carbon::instance($rawValue);

        if ($fieldDefinition->getType() === 'time') {
            return $carbon->toTimeString('minute');
        }

        return $carbon;
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string
    {
        return null;
    }
}

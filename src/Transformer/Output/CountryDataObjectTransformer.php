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

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Output\Traits\ChoiceTargetTransformerTrait;
use FormBuilderBundle\Transformer\Target\TargetAwareData;
use FormBuilderBundle\Transformer\Target\TargetAwareValue;
use Symfony\Component\Form\FormInterface;

class CountryDataObjectTransformer implements OutputTransformerInterface
{
    use ChoiceTargetTransformerTrait;

    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed
    {
        if ($fieldDefinition->getType() !== 'country') {
            return null;
        }

        return new TargetAwareValue([$this, 'getTargetAwareValue']);
    }

    public function getTargetAwareValue(TargetAwareData $targetAwareData): mixed
    {
        $rawValue = $targetAwareData->getRawValue();
        $validType = is_array($rawValue) || is_scalar($rawValue);

        if ($validType === false) {
            return null;
        }

        $target = $targetAwareData->getTarget();

        return $this->parseChoiceValue($target, $rawValue);
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string
    {
        return null;
    }
}

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

namespace FormBuilderBundle\Transformer\Target;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class TargetAwareData
{
    public function __construct(
        protected mixed $target,
        protected FieldDefinitionInterface $fieldDefinition,
        protected FormInterface $formField,
        protected mixed $rawValue,
        protected ?string $locale
    ) {
    }

    public function getTarget(): mixed
    {
        return $this->target;
    }

    public function getFieldDefinition(): FieldDefinitionInterface
    {
        return $this->fieldDefinition;
    }

    public function getFormField(): FormInterface
    {
        return $this->formField;
    }

    public function getRawValue(): mixed
    {
        return $this->rawValue;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}

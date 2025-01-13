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

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FormAssembleEvent extends Event
{
    protected array $formData = [];

    public function __construct(
        protected FormOptionsResolver $formOptionsResolver,
        protected FormDefinitionInterface $formDefinition,
        protected ?FormInterface $form = null,
        protected bool $headless = false
    ) {
    }

    public function getFormOptionsResolver(): FormOptionsResolver
    {
        return $this->formOptionsResolver;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
    }

    public function isHeadless(): bool
    {
        return $this->headless;
    }
}

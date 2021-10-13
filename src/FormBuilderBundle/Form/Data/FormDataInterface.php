<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDataInterface
{
    public function getFormDefinition(): FormDefinitionInterface;

    public function getData(): array;

    public function getFieldValue(string $name): mixed;

    public function setFieldValue(string $name, mixed $value);

    public function hasAttachments(): bool;

    public function getAttachments(): array;

    public function addAttachment(array $attachmentFileInfo): void;
}

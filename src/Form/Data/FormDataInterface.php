<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Stream\File;

interface FormDataInterface
{
    public function getFormDefinition(): FormDefinitionInterface;

    public function getData(): array;

    public function getFieldValue(string $name): mixed;

    public function setFieldValue(string $name, mixed $value);

    public function hasAttachments(): bool;

    /**
     * @return array<int, File>
     */
    public function getAttachments(): array;

    public function addAttachment(File $attachmentFile): void;
}

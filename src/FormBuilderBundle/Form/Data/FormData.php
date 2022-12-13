<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Stream\File;

class FormData implements FormDataInterface
{
    protected FormDefinitionInterface $formDefinition;
    protected array $data = [];
    protected array $attachments = [];

    public function __construct(FormDefinitionInterface $formDefinition, array $data = [])
    {
        $this->formDefinition = $formDefinition;
        $this->data = $data;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function hasAttachments(): bool
    {
        return count($this->attachments) > 0;
    }

    public function getAttachments(): array
    {
        return array_values($this->attachments);
    }

    public function addAttachment(File $attachmentFile): void
    {
        if (array_key_exists($attachmentFile->getId(), $this->attachments)) {
            return;
        }

        $this->attachments[$attachmentFile->getId()] = $attachmentFile;
    }

    public function getFieldValue(string $name): mixed
    {
        $array = $this->getData();
        if (isset($array[$name])) {
            return $array[$name];
        }

        return null;
    }

    public function setFieldValue(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function __set(string $name, mixed $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        if (!is_string($name)) {
            return false;
        }

        $data = $this->getData();

        return isset($data[$name]);
    }

    public function __get(string $name): mixed
    {
        return $this->getFieldValue($name);
    }
}

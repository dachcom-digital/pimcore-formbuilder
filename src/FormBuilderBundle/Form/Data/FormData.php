<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormData extends Form implements FormDataInterface
{
    protected FormDefinitionInterface $formDefinition;
    protected array $data = [];
    protected array $attachments = [];

    public function __construct(FormDefinitionInterface $formDefinition)
    {
        $this->formDefinition = $formDefinition;
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
        return $this->attachments;
    }

    public function addAttachment(array $attachmentFileInfo): void
    {
        $this->attachments[] = $attachmentFileInfo;
    }

    public function getFieldValue(string $name)
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

    public function __set(string $name, $value): void
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

    public function __get(string $name)
    {
        return $this->getFieldValue($name);
    }
}

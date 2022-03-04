<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Stream\File;

class FormData extends Form implements FormDataInterface
{
    /**
     * @var FormDefinitionInterface
     */
    protected $formDefinition;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $attachments = [];

    /**
     * @param FormDefinitionInterface $formDefinition
     */
    public function __construct(FormDefinitionInterface $formDefinition)
    {
        $this->formDefinition = $formDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormDefinition()
    {
        return $this->formDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttachments()
    {
        return count($this->attachments) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return array_values($this->attachments);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(File $attachmentFile)
    {
        if (array_key_exists($attachmentFile->getId(), $this->attachments)) {
            return;
        }

        $this->attachments[$attachmentFile->getId()] = $attachmentFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldValue(string $name)
    {
        $array = $this->getData();
        if (isset($array[$name])) {
            return $array[$name];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldValue(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $data = $this->getData();

        return isset($data[$name]);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }
}

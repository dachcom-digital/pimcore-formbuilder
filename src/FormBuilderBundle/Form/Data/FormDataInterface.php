<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDataInterface
{
    /**
     * @return FormDefinitionInterface
     */
    public function getFormDefinition();

    /**
     * @return array
     */
    public function getData();

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getFieldValue(string $name);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setFieldValue(string $name, $value);

    /**
     * @param string $fieldId
     * @param mixed  $value
     */
    public function replaceValueByFieldId(string $fieldId, $value);

    /**
     * @return bool
     */
    public function hasAttachments();

    /**
     * @return array
     */
    public function getAttachments();

    /**
     * @param array $attachmentFileInfo
     */
    public function addAttachment(array $attachmentFileInfo);
}

<?php

namespace FormBuilderBundle\Form\Data;

use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Model\FormDefinitionInterface;

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
        return $this->attachments;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(array $attachmentFileInfo)
    {
        $this->attachments[] = $attachmentFileInfo;
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
     * {@inheritdoc}
     */
    public function replaceValueByFieldId(string $fieldId, $value)
    {
        if (!is_array($this->data)) {
            return;
        }

        $found = false;
        $formId = sprintf('formbuilder_%s', $this->formDefinition->getId());

        $arrayIterator = new \RecursiveArrayIterator($this->data);
        $iterator = new \RecursiveIteratorIterator($arrayIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $key => $leafValue) {
            $change = false;
            $keys = [$formId];

            foreach (range(0, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
                if (join('_', $keys) === $fieldId) {
                    $change = true;
                }
            }

            if ($change === false) {
                continue;
            }

            $found = true;
            $currentDepth = $iterator->getDepth();
            for ($subDepth = $currentDepth; $subDepth >= 0; $subDepth--) {
                /** @var \ArrayIterator $subIterator */
                $subIterator = $iterator->getSubIterator($subDepth);

                if ($subDepth === $currentDepth) {
                    $storeValue = $value;
                } else {
                    /** @var \ArrayIterator $nextSubIterator */
                    $nextSubIterator = $iterator->getSubIterator(($subDepth + 1));
                    $storeValue = $nextSubIterator->getArrayCopy();
                }

                $subIterator->offsetSet($subIterator->key(), $storeValue);
            }
        }

        $this->data = $iterator->getArrayCopy();

        if ($found === false) {
            $this->data[str_replace(sprintf('%s_', $formId), '', $fieldId)] = $value;
        }
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

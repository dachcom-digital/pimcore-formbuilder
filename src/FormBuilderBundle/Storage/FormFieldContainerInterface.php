<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormFieldContainerInterface
{
    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator);

    /**
     * @param int $order
     */
    public function setOrder(int $order);

    /**
     * @return int
     */
    public function getOrder();

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setDisplayName(string $name);

    /**
     * @return mixed
     */
    public function getDisplayName();

    /**
     * @param string $type
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $subType
     */
    public function setSubType(string $subType);

    /**
     * @return string
     */
    public function getSubType();

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration = []);

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @param FormFieldInterface[] $fields
     */
    public function setFields(array $fields = []);

    /**
     * @return FormFieldInterface[]
     */
    public function getFields();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return bool
     */
    public function isUpdated();
}

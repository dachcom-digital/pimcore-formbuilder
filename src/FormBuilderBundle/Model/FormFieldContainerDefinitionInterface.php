<?php

namespace FormBuilderBundle\Model;

interface FormFieldContainerDefinitionInterface extends FieldDefinitionInterface
{
    /**
     * @param int $order
     */
    public function setOrder(int $order);

    /**
     * @param string $name
     */
    public function setName(string $name);

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
}

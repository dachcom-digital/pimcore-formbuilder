<?php

namespace FormBuilderBundle\Model;

interface FormFieldDefinitionInterface extends FieldDefinitionInterface
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
     * @param array $options
     */
    public function setOptions(array $options = []);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param array $options
     */
    public function setOptional(array $options = []);

    /**
     * @return array
     */
    public function getOptional();

    /**
     * @param array $constraints
     */
    public function setConstraints(array $constraints = []);

    /**
     * @return array
     */
    public function getConstraints();
}

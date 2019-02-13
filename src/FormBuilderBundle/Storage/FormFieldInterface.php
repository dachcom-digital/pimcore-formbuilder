<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormFieldInterface
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

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return bool
     */
    public function isUpdated();
}

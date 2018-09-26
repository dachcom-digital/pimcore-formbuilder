<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormFieldInterface
{
    /**
     * @param Translator $translator
     *
     * @return FormFieldInterface|void
     */
    public function setTranslator(Translator $translator);

    /**
     * @return int
     */
    public function getOrder();

    /**
     * @param int $order
     *
     * @return FormFieldInterface|void
     */
    public function setOrder(int $order);

    /**
     * @param string $name
     *
     * @return FormFieldInterface|void
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return FormFieldInterface|void
     */
    public function setDisplayName(string $name);

    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @param string $type
     *
     * @return FormFieldInterface|void
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param array $options
     *
     * @return FormFieldInterface|void
     */
    public function setOptions(array $options = []);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param array $options
     *
     * @return FormFieldInterface|void
     */
    public function setOptional(array $options = []);

    /**
     * @return array
     */
    public function getOptional();

    /**
     * @param array $constraints
     *
     * @return FormFieldInterface|void
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

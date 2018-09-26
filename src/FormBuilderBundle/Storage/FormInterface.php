<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormInterface
{
    /**
     * @param Translator $translator
     *
     * @return FormInterface|void
     */
    public function setTranslator(Translator $translator);

    /**
     * @param string $newName
     *
     * @return true
     */
    public function rename(string $newName);

    /**
     * @return mixed
     */
    public function delete();

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param string $name
     *
     * @return FormInterface|void
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function save();

    /**
     * @param mixed $date
     *
     * @return FormInterface|void
     */
    public function setDate($date);

    /**
     * @return mixed
     */
    public function getDate();

    /**
     * @param array $config
     *
     * @return FormInterface|void
     */
    public function setConfig(array $config);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param array $data
     *
     * @return FormInterface|void
     */
    public function setConditionalLogic(array $data);

    /**
     * @return array
     */
    public function getConditionalLogic();

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $optional
     *
     * @return FormInterface|void
     */
    public function addDynamicField(string $name, string $type, array $options, array $optional = []);

    /**
     * @param array $fields
     *
     * @return FormInterface|void
     */
    public function setFields(array $fields);

    /**
     * @return array
     */
    public function getFields();

    /**
     * @param string $type
     *
     * @return FormFieldInterface[]
     */
    public function getFieldsByType(string $type);

    /**
     * @param string $name
     *
     * @return FormFieldInterface|null
     */
    public function getField(string $name);

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getFieldType(string $name);

    /**
     * @param string $name
     *
     * @return string|array|null
     */
    public function getFieldValue(string $name);
}

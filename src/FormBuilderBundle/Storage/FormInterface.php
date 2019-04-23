<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormInterface
{
    /**
     * @param int $id
     *
     * @return FormInterface
     *
     * @throws \Exception
     */
    public static function getById(int $id);

    /**
     * @param string $name
     *
     * @return FormInterface
     *
     * @throws \Exception
     */
    public static function getByName(string $name);

    /**
     * @param int $id
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getNameById(int $id);

    /**
     * @param string $name
     *
     * @return int
     *
     * @throws \Exception
     */
    public static function getIdByName(string $name);

    /**
     * @return FormInterface[]
     */
    public static function getAll();

    /**
     * @throws \Exception
     */
    public function save();

    /**
     * @throws \Exception
     */
    public function delete();

    /**
     * @param string $newName
     *
     * @throws \Exception
     */
    public function rename(string $newName);

    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator);

    /**
     * @return null|int
     */
    public function getId();

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $groupName
     */
    public function setGroup(string $groupName);

    /**
     * @return null|string
     */
    public function getGroup();

    /**
     * @param string $date
     */
    public function setCreationDate(string $date);

    /**
     * @return string
     */
    public function getCreationDate();

    /**
     * @param string $date
     */
    public function setModificationDate(string $date);

    /**
     * @return string
     */
    public function getModificationDate();

    /**
     * @param int $userId
     *
     * @return int
     */
    public function setModifiedBy(int $userId);

    /**
     * @return int
     */
    public function getModifiedBy();

    /**
     * @param int $userId
     *
     * @return int
     */
    public function setCreatedBy(int $userId);

    /**
     * @return int
     */
    public function getCreatedBy();

    /**
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param array $data
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
     * @throws \Exception
     */
    public function addDynamicField(string $name, string $type, array $options = [], array $optional = []);

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function removeDynamicField(string $name);

    /**
     * @param FormFieldInterface[] $fields
     */
    public function setFields(array $fields);

    /**
     * @return FormFieldInterface[]
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
     * @return null|FormFieldInterface
     */
    public function getField(string $name);

    /**
     * @param string $name
     *
     * @return null|FormFieldContainerInterface
     *
     * @throws \Exception
     */
    public function getFieldContainer(string $name);

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getFieldType(string $name);

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

<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormInterface
{
    public function setTranslator(Translator $translator);

    public function rename($newName);

    public function delete();

    public function getId();

    public function setName($name);

    public function getName();

    public function save();

    public function setDate($date);

    public function getDate();

    public function setConfig($config);

    public function getConfig();

    public function setFields(array $fields);

    public function getFields();

    public function getFieldsByType($type);

    public function getField($name);

    public function getFieldType($name);

    public function getFieldValue($name);
}
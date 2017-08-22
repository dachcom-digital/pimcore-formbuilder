<?php

namespace FormBuilderBundle\Storage;

use Pimcore\Translation\Translator;

interface FormFieldInterface
{
    public function setTranslator(Translator $translator);

    public function getOrder();

    public function setOrder($order);

    public function setName($name);

    public function getName();

    public function setDisplayName($name);

    public function getDisplayName();

    public function setType($type);

    public function getType();

    public function setOptions($options = []);

    public function getOptions();

    public function setOptional($options = []);

    public function getOptional();

    public function setConstraints($constraints = []);

    public function getConstraints();

    public function toArray();
}
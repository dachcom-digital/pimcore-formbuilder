<?php

namespace FormBuilderBundle\Model;

use FormBuilderBundle\Model\Fragment\SubFieldsAwareInterface;

interface FormFieldContainerDefinitionInterface extends FieldDefinitionInterface, SubFieldsAwareInterface
{
    public function setOrder(int $order): void;

    public function setName(string $name): void;

    public function setDisplayName(string $name): void;

    public function getDisplayName(): string;

    public function setType(string $type): void;

    public function setSubType(string $subType): void;

    public function getSubType(): string;

    public function setConfiguration(array $configuration = []): void;

    public function getConfiguration(): array;
}

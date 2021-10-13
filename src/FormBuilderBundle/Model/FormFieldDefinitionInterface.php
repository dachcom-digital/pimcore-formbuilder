<?php

namespace FormBuilderBundle\Model;

interface FormFieldDefinitionInterface extends FieldDefinitionInterface
{
    public function setOrder(int $order): void;

    public function setName(string $name): void;

    public function setDisplayName(string $name): void;

    public function getDisplayName(): string;

    public function setType(string $type): void;

    public function setOptions(array $options = []): void;

    public function getOptions(): array;

    public function setOptional(array $options = []): void;

    public function getOptional(): array;

    public function setConstraints(array $constraints = []): void;

    public function getConstraints(): array;
}

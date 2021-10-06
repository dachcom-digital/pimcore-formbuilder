<?php

namespace FormBuilderBundle\Model;

interface FieldDefinitionInterface
{
    public function getOrder(): int;

    public function getName(): string;

    public function getType(): string;
}

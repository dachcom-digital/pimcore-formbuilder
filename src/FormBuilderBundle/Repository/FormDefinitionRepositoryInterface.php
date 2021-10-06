<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDefinitionRepositoryInterface
{
    public function findById(int $id): ?FormDefinitionInterface;

    public function findByName(string $name): ?FormDefinitionInterface;

    public function findNameById(int $id): ?FormDefinitionInterface;

    public function findIdByName(string $name): ?FormDefinitionInterface;

    public function findAll(): array;
}

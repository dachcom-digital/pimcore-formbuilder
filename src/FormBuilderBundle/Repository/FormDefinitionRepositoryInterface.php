<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDefinitionRepositoryInterface
{
    public function findById(int $id): ?FormDefinitionInterface;

    public function findByName(string $name): ?FormDefinitionInterface;

    public function findNameById(int $id): ?string;

    public function findIdByName(string $name): ?int;

    /**
     * @return FormDefinitionInterface[]
     */
    public function findAll(): array;
}

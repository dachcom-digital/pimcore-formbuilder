<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowRepositoryInterface
{
    public function findById(int $id): ?OutputWorkflowInterface;

    public function findByNameAndFormId(string $name, int $formId): ?OutputWorkflowInterface;

    public function findNameById(int $id): ?string;

    public function findIdByName(string $name): ?int;

    public function findAll(): array;
}

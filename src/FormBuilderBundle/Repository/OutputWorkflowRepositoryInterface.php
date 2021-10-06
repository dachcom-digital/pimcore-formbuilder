<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowRepositoryInterface
{
    public function findById(int $id): ?OutputWorkflowInterface;

    public function findByNameAndFormId(string $name, int $formId): ?OutputWorkflowInterface;

    public function findNameById(int $id): ?OutputWorkflowInterface;

    public function findIdByName(string $name): ?OutputWorkflowInterface;

    public function findAll(): array;
}

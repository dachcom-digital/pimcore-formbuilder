<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\OutputWorkflow;
use FormBuilderBundle\Model\OutputWorkflowInterface;

class OutputWorkflowRepository implements OutputWorkflowRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(OutputWorkflow::class);
    }

    public function findById(int $id): ?OutputWorkflowInterface
    {
        if ($id < 1) {
            return null;
        }

        return $this->repository->find($id);
    }

    public function findByNameAndFormId(string $name, int $formId): ?OutputWorkflowInterface
    {
        if (empty($name) || empty($formId)) {
            return null;
        }

        return $this->repository->findOneBy(['name' => $name, 'formDefinition' => $formId]);
    }

    public function findNameById($id): ?string
    {
        $outputWorkflow = $this->findById($id);

        if (null === $outputWorkflow) {
            return null;
        }

        return $outputWorkflow->getName();
    }

    public function findIdByName(string $name): ?int
    {
        $outputWorkflow = $this->repository->findOneBy(['name' => $name]);

        if (null === $outputWorkflow) {
            return null;
        }

        return $outputWorkflow->getId();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}

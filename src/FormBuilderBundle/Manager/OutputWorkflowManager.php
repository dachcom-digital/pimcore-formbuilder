<?php

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Factory\OutputWorkflowFactoryInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Repository\OutputWorkflowRepositoryInterface;

class OutputWorkflowManager
{
    protected OutputWorkflowFactoryInterface $outputWorkflowFactory;
    protected OutputWorkflowRepositoryInterface $outputWorkflowRepository;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        OutputWorkflowFactoryInterface $outputWorkflowFactory,
        OutputWorkflowRepositoryInterface $outputWorkflowRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->outputWorkflowFactory = $outputWorkflowFactory;
        $this->outputWorkflowRepository = $outputWorkflowRepository;
        $this->entityManager = $entityManager;
    }

    public function getById(int $id): ?OutputWorkflowInterface
    {
        return $this->outputWorkflowRepository->findById($id);
    }

    public function save(array $data, ?int $id = null): ?OutputWorkflowInterface
    {
        if (!is_null($id)) {
            $outputWorkflow = $this->outputWorkflowRepository->findById($id);
        } else {
            $outputWorkflow = $this->outputWorkflowFactory->createOutputWorkflow();
        }

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            return null;
        }

        if (isset($data['name'])) {
            $outputWorkflow->setName($data['name']);
        }

        if (isset($data['formDefinition'])) {
            $outputWorkflow->setFormDefinition($data['formDefinition']);
        }

        $this->entityManager->persist($outputWorkflow);
        $this->entityManager->flush();

        return $outputWorkflow;
    }

    public function saveRawEntity(OutputWorkflowInterface $outputWorkflow): void
    {
        $this->entityManager->persist($outputWorkflow);
        $this->entityManager->flush();
    }

    public function delete(int $id): void
    {
        $outputWorkflow = $this->outputWorkflowRepository->findById($id);

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            return;
        }

        $this->entityManager->remove($outputWorkflow);
        $this->entityManager->flush();
    }

    public function getFormOutputWorkflowByName(string $name, int $formId): ?OutputWorkflowInterface
    {
        return $this->outputWorkflowRepository->findByNameAndFormId($name, $formId);
    }
}

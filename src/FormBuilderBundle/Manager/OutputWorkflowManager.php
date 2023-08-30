<?php

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Factory\OutputWorkflowFactoryInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Repository\OutputWorkflowRepositoryInterface;

class OutputWorkflowManager
{
    public function __construct(
        protected OutputWorkflowFactoryInterface $outputWorkflowFactory,
        protected OutputWorkflowRepositoryInterface $outputWorkflowRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function getById(int $id): ?OutputWorkflowInterface
    {
        return $this->outputWorkflowRepository->findById($id);
    }

    /**
     * @throws \Exception
     */
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

        if (array_key_exists('name', $data)) {
            $outputWorkflow->setName($data['name']);
        }

        if (array_key_exists('formDefinition', $data)) {
            $outputWorkflow->setFormDefinition($data['formDefinition']);
        }

        if (array_key_exists('funnelAware', $data)) {
            $outputWorkflow->setFunnelWorkflow($data['funnelAware'] === true);
        }

        $this->entityManager->persist($outputWorkflow);
        $this->entityManager->flush();

        return $outputWorkflow;
    }

    /**
     * @throws \Exception
     */
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

<?php

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Factory\OutputWorkflowFactoryInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Repository\OutputWorkflowRepositoryInterface;

class OutputWorkflowManager
{
    /**
     * @var OutputWorkflowFactoryInterface
     */
    protected $outputWorkflowFactory;

    /**
     * @var OutputWorkflowRepositoryInterface
     */
    protected $outputWorkflowRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param OutputWorkflowFactoryInterface    $outputWorkflowFactory
     * @param OutputWorkflowRepositoryInterface $outputWorkflowRepository
     * @param EntityManagerInterface            $entityManager
     */
    public function __construct(
        OutputWorkflowFactoryInterface $outputWorkflowFactory,
        OutputWorkflowRepositoryInterface $outputWorkflowRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->outputWorkflowFactory = $outputWorkflowFactory;
        $this->outputWorkflowRepository = $outputWorkflowRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     *
     * @return OutputWorkflowInterface|null
     */
    public function getById(int $id)
    {
        return $this->outputWorkflowRepository->findById($id);
    }

    /**
     * @param array    $data
     * @param null|int $id
     *
     * @return OutputWorkflowInterface|null
     *
     * @throws \Exception
     */
    public function save(array $data, $id = null)
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

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     *
     * @throws \Exception
     */
    public function saveRawEntity(OutputWorkflowInterface $outputWorkflow)
    {
        $this->entityManager->persist($outputWorkflow);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        $outputWorkflow = $this->outputWorkflowRepository->findById($id);

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            return;
        }

        $this->entityManager->remove($outputWorkflow);
        $this->entityManager->flush();
    }

    /**
     * @param string $name
     * @param int    $formId
     *
     * @return OutputWorkflowInterface|null
     */
    public function getFormOutputWorkflowByName(string $name, int $formId)
    {
        return $this->outputWorkflowRepository->findByNameAndFormId($name, $formId);
    }
}

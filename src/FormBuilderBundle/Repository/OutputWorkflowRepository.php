<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\OutputWorkflow;

class OutputWorkflowRepository implements OutputWorkflowRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(OutputWorkflow::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        if ($id < 1) {
            return null;
        }

        return $this->repository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByNameAndFormId(string $name, int $formId)
    {
        if (empty($name) || empty($formId)) {
            return null;
        }

        return $this->repository->findOneBy(['name' => $name, 'form' => $formId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findNameById($id)
    {
        $outputWorkflow = $this->findById($id);

        return $outputWorkflow->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function findIdByName(string $name)
    {
        $outputWorkflow = $this->findByName($name);

        return $outputWorkflow->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }
}

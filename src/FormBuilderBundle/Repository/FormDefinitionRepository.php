<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormDefinitionRepository implements FormDefinitionRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(FormDefinition::class);
    }

    public function findById($id): ?FormDefinitionInterface
    {
        if ($id < 1) {
            return null;
        }

        return $this->repository->find($id);
    }

    public function findByName(string $name): ?FormDefinitionInterface
    {
        if (empty($name)) {
            return null;
        }

        return $this->repository->findOneBy(['name' => $name]);
    }

    public function findNameById($id): ?string
    {
        $form = $this->findById($id);

        if (!$form instanceof FormDefinitionInterface) {
            return null;
        }

        return $form->getName();
    }

    public function findIdByName(string $name): ?int
    {
        $form = $this->findByName($name);

        if (!$form instanceof FormDefinitionInterface) {
            return null;
        }

        return $form->getId();
    }

    public function findAll(): array
    {
        return  $this->repository->findAll();
    }
}

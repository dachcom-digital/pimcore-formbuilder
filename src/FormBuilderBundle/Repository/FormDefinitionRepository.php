<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Form\Data\Connector\FormDataConnectorInterface;

class FormDefinitionRepository implements FormDefinitionRepositoryInterface
{
    protected FormDataConnectorInterface $formDataConnector;
    protected EntityRepository $repository;

    public function __construct(
        FormDataConnectorInterface $formDataConnector,
        EntityManagerInterface $entityManager
    ) {
        $this->formDataConnector = $formDataConnector;
        $this->repository = $entityManager->getRepository(FormDefinition::class);
    }

    public function findById($id): ?FormDefinitionInterface
    {
        if ($id < 1) {
            return null;
        }

        /** @var FormDefinitionInterface $object */
        $object = $this->repository->find($id);

        return $this->assembleSingle($object);
    }

    public function findByName(string $name): ?FormDefinitionInterface
    {
        if (empty($name)) {
            return null;
        }

        /** @var FormDefinitionInterface $object */
        $object = $this->repository->findOneBy(['name' => $name]);

        return $this->assembleSingle($object);
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
        $objects = $this->repository->findAll();

        return $this->assembleMultiple($objects);
    }

    protected function assembleSingle(?FormDefinitionInterface $formDefinition): ?FormDefinitionInterface
    {
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return null;
        }

        $this->formDataConnector->assignRelationDataToFormObject($formDefinition);

        return $formDefinition;
    }

    protected function assembleMultiple(array $formDefinitions): array
    {
        $assembledFormDefinitions = [];
        foreach ($formDefinitions as $formDefinition) {
            if ($formDefinition instanceof FormDefinitionInterface) {
                $this->formDataConnector->assignRelationDataToFormObject($formDefinition);
                $assembledFormDefinitions[] = $formDefinition;
            }
        }

        return $assembledFormDefinitions;
    }
}

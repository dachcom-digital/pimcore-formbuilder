<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Form\Data\Connector\FormDataConnectorInterface;

class FormDefinitionRepository implements FormDefinitionRepositoryInterface
{
    /**
     * @var FormDataConnectorInterface
     */
    protected $formDataConnector;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @param FormDataConnectorInterface $formDataConnector
     * @param EntityManagerInterface     $entityManager
     */
    public function __construct(
        FormDataConnectorInterface $formDataConnector,
        EntityManagerInterface $entityManager
    ) {
        $this->formDataConnector = $formDataConnector;
        $this->repository = $entityManager->getRepository(FormDefinition::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        if ($id < 1) {
            return null;
        }

        /** @var FormDefinitionInterface $object */
        $object = $this->repository->find($id);

        return $this->assembleSingle($object);
    }

    /**
     * {@inheritdoc}
     */
    public function findByName(string $name)
    {
        if (empty($name)) {
            return null;
        }

        /** @var FormDefinitionInterface $object */
        $object = $this->repository->findOneBy(['name' => $name]);

        return $this->assembleSingle($object);
    }

    /**
     * {@inheritdoc}
     */
    public function findNameById($id)
    {
        $form = $this->findById($id);

        return $form->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function findIdByName(string $name)
    {
        $form = $this->findByName($name);

        return $form->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $objects = $this->repository->findAll();

        return $this->assembleMultiple($objects);
    }

    /**
     * @param FormDefinitionInterface|null $formDefinition
     *
     * @return FormDefinitionInterface|null
     */
    protected function assembleSingle(?FormDefinitionInterface $formDefinition)
    {
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return null;
        }

        $this->formDataConnector->assignRelationDataToFormObject($formDefinition);

        return $formDefinition;
    }

    /**
     * @param FormDefinitionInterface[] $formDefinitions
     *
     * @return FormDefinitionInterface[]
     */
    protected function assembleMultiple(array $formDefinitions)
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

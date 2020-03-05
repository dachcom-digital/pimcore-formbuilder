<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\Form;
use FormBuilderBundle\Model\FormInterface;
use FormBuilderBundle\Storage\DataConnector\FormDataConnectorInterface;

class FormRepository implements FormRepositoryInterface
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
        $this->repository = $entityManager->getRepository(Form::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        if ($id < 1) {
            return null;
        }

        /** @var FormInterface $object */
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

        /** @var FormInterface $object */
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
     * @param FormInterface|null $form
     *
     * @return FormInterface|null
     */
    protected function assembleSingle(?FormInterface $form)
    {
        if (!$form instanceof FormInterface) {
            return null;
        }

        $this->formDataConnector->assignRelationDataToFormObject($form);

        return $form;
    }

    /**
     * @param FormInterface[] $forms
     *
     * @return FormInterface[]
     */
    protected function assembleMultiple(array $forms)
    {
        $assembledForms = [];
        foreach ($forms as $form) {
            if ($form instanceof FormInterface) {
                $this->formDataConnector->assignRelationDataToFormObject($form);
                $assembledForms[] = $form;
            }
        }

        return $assembledForms;
    }
}

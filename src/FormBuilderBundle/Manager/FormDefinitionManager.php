<?php

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Repository\FormDefinitionRepositoryInterface;
use FormBuilderBundle\Form\Data\Connector\FormDataConnectorInterface;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Model\User;

class FormDefinitionManager
{
    protected FormDefinitionFactoryInterface $formDefinitionFactory;
    protected FormDefinitionRepositoryInterface $formDefinitionRepository;
    protected FormDataConnectorInterface $formDataConnector;
    protected TokenStorageUserResolver $storageUserResolver;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        FormDefinitionFactoryInterface $formDefinitionFactory,
        FormDefinitionRepositoryInterface $formDefinitionRepository,
        FormDataConnectorInterface $formDataConnector,
        TokenStorageUserResolver $storageUserResolver,
        EntityManagerInterface $entityManager
    ) {
        $this->formDefinitionFactory = $formDefinitionFactory;
        $this->formDefinitionRepository = $formDefinitionRepository;
        $this->formDataConnector = $formDataConnector;
        $this->storageUserResolver = $storageUserResolver;
        $this->entityManager = $entityManager;
    }

    public function getById(int $id): ?FormDefinitionInterface
    {
        return $this->formDefinitionRepository->findById($id);
    }

    public function configurationFileExists(int $id): bool
    {
        return $this->formDataConnector->formHasAvailableConfigurationFile($id);
    }

    public function getConfigurationPath(int $id): string
    {
        return $this->formDataConnector->getConfigurationPathOfForm($id);
    }

    /**
     * @return array<int, FormDefinitionInterface>
     */
    public function getAll(): array
    {
        return $this->formDefinitionRepository->findAll();
    }

    public function getIdByName(string $name): ?FormDefinitionInterface
    {
        return $this->formDefinitionRepository->findByName($name);
    }

    /**
     * @throws \Exception
     */
    public function save(array $data, ?int $id = null): ?FormDefinitionInterface
    {
        $isUpdate = false;
        if (!is_null($id)) {
            $isUpdate = true;
            $form = $this->getById($id);
        } else {
            $form = $this->formDefinitionFactory->createFormDefinition();
        }

        if (!$form instanceof FormDefinitionInterface) {
            return null;
        }

        $this->updateFormAttributes($data, $form, $isUpdate);
        $this->updateFields(isset($data['form_fields']) ? $data['form_fields'] : [], $form);

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $this->formDataConnector->storeFormData($form);

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function saveRawEntity(FormDefinitionInterface $form): void
    {
        $date = new \DateTime();
        $form->setModificationDate($date);

        $this->entityManager->persist($form);
        $this->entityManager->flush();
    }

    public function delete(int $id): void
    {
        $form = $this->getById($id);

        if (!$form instanceof FormDefinitionInterface) {
            return;
        }

        $this->formDataConnector->deleteFormData($form);

        $this->entityManager->remove($form);
        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function rename(int $id, string $newName): ?FormDefinitionInterface
    {
        $form = $this->getById($id);

        if (!$form instanceof FormDefinitionInterface) {
            return null;
        }

        $form->setName($newName);

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        return $form;
    }

    protected function updateFormAttributes(array $data, FormDefinitionInterface $form, bool $isUpdate = false): void
    {
        $form->setName((string) $data['form_name']);

        if (isset($data['form_group'])) {
            $form->setGroup($data['form_group']);
        }

        $date = new \DateTime();
        if ($isUpdate === false) {
            $form->setCreationDate($date);
            $form->setCreatedBy($this->getAdminUserId());
        }

        $form->setModificationDate($date);
        $form->setModifiedBy($this->getAdminUserId());

        if (isset($data['form_config']) && is_array($data['form_config'])) {
            $form->setConfig($data['form_config']);
        }

        if (isset($data['form_conditional_logic']) && is_array($data['form_conditional_logic'])) {
            $form->setConditionalLogic($data['form_conditional_logic']);
        }
    }

    /**
     * @throws \Exception
     */
    public function updateFields(array $data, FormDefinitionInterface $form): void
    {
        $order = 0;
        $fields = [];

        foreach ($this->getValue($data, 'fields', []) as $fieldData) {
            //allow some space for dynamic fields.
            $order += 100;

            $fieldType = $this->getValue($fieldData, 'type');
            $fieldName = $this->getValue($fieldData, 'name');

            if ($fieldType === 'container') {
                $field = $this->generateFormFieldContainer($form, $fieldData, $order);
            } else {
                $field = $this->generateFormField($form, $fieldData, $order);
            }

            $fields[$fieldName] = $field;
        }

        $form->setFields($fields);
    }

    /**
     * @throws \Exception
     */
    protected function generateFormFieldContainer(FormDefinitionInterface $form, array $fieldData, int $order): FormFieldContainerDefinitionInterface
    {
        $fieldType = $this->getValueAsString($fieldData, 'type');
        $fieldSubType = $this->getValueAsString($fieldData, 'sub_type');
        $fieldName = $this->getValueAsString($fieldData, 'name');
        $fieldDisplayName = $this->getValueAsString($fieldData, 'display_name');
        $configParameter = $this->getValue($fieldData, 'configuration');
        $containerFields = $this->getValue($fieldData, 'fields');

        $fieldContainer = $form->getFieldContainer($fieldName);

        if (!$fieldContainer instanceof FormFieldContainerDefinitionInterface) {
            $fieldContainer = $this->formDefinitionFactory->createFormFieldContainerDefinition();
        }

        $fieldContainer->setName($fieldName);
        $fieldContainer->setDisplayName($fieldDisplayName);
        $fieldContainer->setType($fieldType);
        $fieldContainer->setSubType($fieldSubType);
        $fieldContainer->setOrder($order);

        if (!empty($configParameter) && is_array($configParameter)) {
            $fieldContainer->setConfiguration($configParameter);
        } else {
            $fieldContainer->setConfiguration([]);
        }

        // add sub-fields to container
        if (is_array($containerFields) && count($containerFields) > 0) {
            $parsedContainerFields = [];
            $subOrder = 0;
            foreach ($containerFields as $containerFieldData) {
                //allow some space for dynamic fields.
                $subOrder += 100;
                $parsedContainerFields[] = $this->generateFormField($form, $containerFieldData, $subOrder);
            }
            $fieldContainer->setFields($parsedContainerFields);
        } else {
            $fieldContainer->setFields([]);
        }

        return $fieldContainer;
    }

    protected function generateFormField(FormDefinitionInterface $form, array $fieldData, int $order): FormFieldDefinitionInterface
    {
        $fieldType = $this->getValueAsString($fieldData, 'type');
        $fieldName = $this->getValueAsString($fieldData, 'name');
        $fieldDisplayName = $this->getValueAsString($fieldData, 'display_name');
        $constraints = $this->getValue($fieldData, 'constraints');
        $optionsParameter = $this->getValue($fieldData, 'options');
        $optionalParameter = $this->getValue($fieldData, 'optional');

        $field = $form->getField($fieldName);

        if (!$field instanceof FormFieldDefinitionInterface) {
            $field = $this->formDefinitionFactory->createFormFieldDefinition();
        }

        $field->setName($fieldName);
        $field->setDisplayName($fieldDisplayName);
        $field->setType($fieldType);
        $field->setOrder($order);

        if (!empty($optionsParameter) && is_array($optionsParameter)) {
            $field->setOptions($optionsParameter);
        } else {
            $field->setOptions([]);
        }

        if (!empty($optionalParameter) && is_array($optionalParameter)) {
            $field->setOptional($optionalParameter);
        } else {
            $field->setOptional([]);
        }

        if (!empty($constraints) && is_array($constraints)) {
            $field->setConstraints($constraints);
        } else {
            $field->setConstraints([]);
        }

        return $field;
    }

    protected function getValue(array $data, string $value, mixed $default = null): mixed
    {
        return $data[$value] ?? $default;
    }

    protected function getValueAsString(array $data, string $value, string $default = ''): string
    {
        $value = $this->getValue($data, $value, $default);

        return is_string($value) ? $value : $default;
    }

    protected function getAdminUserId(): int
    {
        $user = $this->storageUserResolver->getUser();

        return $user instanceof User ? (int) $user->getId() : 0;
    }
}

<?php

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Factory\FormFactoryInterface;
use FormBuilderBundle\Repository\FormRepositoryInterface;
use FormBuilderBundle\Model\FormInterface;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\DataConnector\FormDataConnectorInterface;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Model\User;

class FormManager
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var FormDataConnectorInterface
     */
    protected $formDataConnector;

    /**
     * TokenStorageUserResolver
     */
    protected $storageUserResolver;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param FormFactoryInterface       $formFactory
     * @param FormRepositoryInterface    $formRepository
     * @param FormDataConnectorInterface $formDataConnector
     * @param TokenStorageUserResolver   $storageUserResolver
     * @param EntityManagerInterface     $entityManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FormRepositoryInterface $formRepository,
        FormDataConnectorInterface $formDataConnector,
        TokenStorageUserResolver $storageUserResolver,
        EntityManagerInterface $entityManager
    ) {
        $this->formFactory = $formFactory;
        $this->formRepository = $formRepository;
        $this->formDataConnector = $formDataConnector;
        $this->storageUserResolver = $storageUserResolver;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     *
     * @return FormInterface|null
     */
    public function getById(int $id)
    {
        return $this->formRepository->findById($id);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function configurationFileExists(int $id)
    {
        return $this->formDataConnector->formHasAvailableConfigurationFile($id);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getConfigurationPath(int $id)
    {
        return $this->formDataConnector->getConfigurationPathOfForm($id);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->formRepository->findAll();
    }

    /**
     * @param string $name
     *
     * @return FormInterface|null
     */
    public function getIdByName(string $name)
    {
        return $this->formRepository->findByName($name);
    }

    /**
     * @param array    $data
     * @param null|int $id
     *
     * @return FormInterface|null
     *
     * @throws \Exception
     */
    public function save(array $data, $id = null)
    {
        $isUpdate = false;
        if (!is_null($id)) {
            $isUpdate = true;
            $form = $this->getById($id);
        } else {
            $form = $this->formFactory->createForm();
        }

        if (!$form instanceof FormInterface) {
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
     * @param FormInterface $form
     *
     * @throws \Exception
     */
    public function saveRawEntity(FormInterface $form)
    {
        $date = new \DateTime();
        $form->setModificationDate($date);

        $this->entityManager->persist($form);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function delete($id)
    {
        $form = $this->getById($id);

        if (!$form instanceof FormInterface) {
            return;
        }

        $this->formDataConnector->deleteFormData($form);

        $this->entityManager->remove($form);
        $this->entityManager->flush();
    }

    /**
     * @param int    $id
     * @param string $newName
     *
     * @return FormInterface|null
     *
     * @throws \Exception
     */
    public function rename(int $id, string $newName)
    {
        $form = $this->getById($id);

        if (!$form instanceof FormInterface) {
            return null;
        }

        $form->setName($newName);

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        return $form;
    }

    /**
     * @param array         $data
     * @param FormInterface $form
     * @param bool          $isUpdate
     */
    protected function updateFormAttributes(array $data, FormInterface $form, $isUpdate = false)
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
     * Updates the contained fields in the form.
     *
     * @param array         $data
     * @param FormInterface $form
     */
    protected function updateFields($data, $form)
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
     * @param FormInterface $form
     * @param array         $fieldData
     * @param int           $order
     *
     * @return FormFieldContainerInterface
     * @throws \Exception
     *
     */
    protected function generateFormFieldContainer(FormInterface $form, array $fieldData, int $order)
    {
        $fieldType = $this->getValueAsString($fieldData, 'type');
        $fieldSubType = $this->getValueAsString($fieldData, 'sub_type');
        $fieldName = $this->getValueAsString($fieldData, 'name');
        $fieldDisplayName = $this->getValueAsString($fieldData, 'display_name');
        $configParameter = $this->getValue($fieldData, 'configuration');
        $containerFields = $this->getValue($fieldData, 'fields');

        $fieldContainer = $form->getFieldContainer($fieldName);

        if (!$fieldContainer instanceof FormFieldContainerInterface) {
            $fieldContainer = $this->formFactory->createFormFieldContainer();
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

    /**
     * @param FormInterface $form
     * @param array         $fieldData
     * @param int           $order
     *
     * @return FormFieldInterface
     */
    protected function generateFormField(FormInterface $form, array $fieldData, int $order)
    {
        $fieldType = $this->getValueAsString($fieldData, 'type');
        $fieldName = $this->getValueAsString($fieldData, 'name');
        $fieldDisplayName = $this->getValueAsString($fieldData, 'display_name');
        $constraints = $this->getValue($fieldData, 'constraints');
        $optionsParameter = $this->getValue($fieldData, 'options');
        $optionalParameter = $this->getValue($fieldData, 'optional');

        $field = $form->getField($fieldName);

        if (!$field instanceof FormFieldInterface) {
            $field = $this->formFactory->createFormField();
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

    /**
     * @param array  $data
     * @param string $value
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getValue($data, $value, $default = null)
    {
        if (isset($data[$value])) {
            return $data[$value];
        }

        return $default;
    }

    /**
     * @param array  $data
     * @param string $value
     * @param string $default
     *
     * @return string
     */
    protected function getValueAsString($data, $value, $default = '')
    {
        $value = $this->getValue($data, $value, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * @return int
     */
    protected function getAdminUserId()
    {
        $user = $this->storageUserResolver->getUser();

        return $user instanceof User ? (int) $user->getId() : 0;
    }
}

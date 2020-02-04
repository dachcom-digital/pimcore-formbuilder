<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Factory\FormFactoryInterface;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;

class FormManager
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * TokenStorageUserResolver.
     */
    protected $storageUserResolver;

    /**
     * @param FormFactoryInterface     $formFactory
     * @param TokenStorageUserResolver $storageUserResolver
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        TokenStorageUserResolver $storageUserResolver
    ) {
        $this->formFactory = $formFactory;
        $this->storageUserResolver = $storageUserResolver;
    }

    /**
     * @param int $id
     *
     * @return FormInterface|null
     */
    public function getById(int $id)
    {
        return $this->formFactory->getFormById($id);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function configurationFileExists(int $id)
    {
        return $this->formFactory->formHasAvailableConfigurationFile($id);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getConfigurationPath(int $id)
    {
        return $this->formFactory->getConfigurationPathOfForm($id);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->formFactory->getAllForms();
    }

    /**
     * @param string $name
     *
     * @return FormInterface|null
     */
    public function getIdByName(string $name)
    {
        return $this->formFactory->getFormIdByName($name);
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
        $form->save();

        return $form;
    }

    /**
     * @param int $id
     *
     * @return FormInterface|null
     *
     * @throws \Exception
     */
    public function delete($id)
    {
        $object = $this->getById($id);

        if (!$object) {
            return null;
        }

        $object->delete();

        return $object;
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
        $object = $this->getById($id);

        if (!$object) {
            return null;
        }

        $object->rename($newName);

        return $object;
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

        $date = date('Y-m-d H:i:s');
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
     * @throws \Exception
     *
     * @return FormFieldContainerInterface
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
     * @return int|null|\Pimcore\Model\User
     */
    protected function getAdminUserId()
    {
        $user = $this->storageUserResolver->getUser();

        return $user instanceof \Pimcore\Model\User ? (int) $user->getId() : 0;
    }
}

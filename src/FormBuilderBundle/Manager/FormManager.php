<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Factory\FormFactoryInterface;
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
     * TokenStorageUserResolver
     */
    protected $storageUserResolver;

    /**
     * FormManager constructor.
     *
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
     * @param $id
     *
     * @return FormInterface|null
     */
    public function getById(int $id)
    {
        return $this->formFactory->getFormById($id);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function configurationFileExists(int $id)
    {
        return $this->formFactory->formHasAvailableConfigurationFile($id);
    }

    /**
     * @param $id
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
     * @param array $data
     * @param null  $id
     *
     * @return FormInterface|null
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

        $this->updateFormAttributes($data, $form, $isUpdate);
        $this->updateFields($data['form_fields'], $form);
        $form->save();

        return $form;
    }

    /**
     * @param $id
     *
     * @return FormInterface|null
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
        $form->setName((string)$data['form_name']);

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
        $counter = 0;
        $fields = [];

        foreach ($this->getValue($data, 'fields', []) as $fieldData) {

            //allow some space for dynamic fields.
            $counter += 100;

            $fieldType = $this->getValue($fieldData, 'type');

            $optionsParameter = $this->getValue($fieldData, 'options');
            $optionalParameter = $this->getValue($fieldData, 'optional');
            $fieldName = $this->getValue($fieldData, 'name');
            $constraints = $this->getValue($fieldData, 'constraints');

            /** @var FormFieldInterface $field */
            $field = $form->getField($fieldName);

            if (!$field) {
                $field = $this->formFactory->createFormField();
                $field->setName($fieldName);
            } elseif ($field->getType() !== $fieldType || !$field->getName()) {
                $field->setName($fieldName);
            }

            $field->setDisplayName($this->getValue($fieldData, 'display_name'));
            $field->setOrder($counter);
            $field->setType($fieldType);

            if (!empty($optionsParameter) && is_array($optionsParameter)) {
                $field->setOptions($optionsParameter);
            }

            if (!empty($optionalParameter) && is_array($optionalParameter)) {
                $field->setOptional($optionalParameter);
            }

            if (!empty($constraints) && is_array($constraints)) {
                $field->setConstraints($constraints);
            }

            $fields[$fieldName] = $field;
        }

        $form->setFields($fields);
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
     * @return int|null|\Pimcore\Model\User
     */
    protected function getAdminUserId()
    {
        $user = $this->storageUserResolver->getUser();
        return $user instanceof \Pimcore\Model\User ? (int)$user->getId() : 0;
    }
}

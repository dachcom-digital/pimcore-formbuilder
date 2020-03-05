<?php

namespace FormBuilderBundle\Storage\DataConnector;

use Symfony\Component\Yaml\Yaml;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Factory\FormFactoryInterface;
use FormBuilderBundle\Model\FormInterface;
use FormBuilderBundle\Storage\FormFieldInterface;

class FormDataConnector implements FormDataConnectorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function assignRelationDataToFormObject(FormInterface $formEntity)
    {
        $formPath = Configuration::STORE_PATH . '/main_' . $formEntity->getId() . '.yml';

        if (!file_exists($formPath)) {
            throw new \Exception(sprintf('configuration yml file for form with id "%d" not found', $formEntity->getId()));
        }

        $data = Yaml::parse(file_get_contents($formPath));

        if (!empty($data['config']) && is_array($data['config'])) {
            $formEntity->setConfig($data['config']);
        }

        if (!empty($data['conditional_logic']) && is_array($data['conditional_logic'])) {
            $formEntity->setConditionalLogic($data['conditional_logic']);
        }

        if (!empty($data['fields'])) {
            $fields = [];
            foreach ($data['fields'] as $field) {
                if ($field['type'] === 'container') {
                    $formField = $this->formFactory->createFormFieldContainer();
                    $this->populateFormField($formField, $field);
                    if (isset($field['fields']) && is_array($field['fields'])) {
                        $subFields = [];
                        foreach ($field['fields'] as $subField) {
                            $subFormField = $this->formFactory->createFormField();
                            $subFields[] = $this->populateFormField($subFormField, $subField);
                        }
                        $formField->setFields($subFields);
                    }
                } else {
                    $formField = $this->formFactory->createFormField();
                    $this->populateFormField($formField, $field);
                }

                $fields[$field['name']] = $formField;
            }

            $formEntity->setFields($fields);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function populateFormField($formField, array $field)
    {
        foreach ($field as $fieldName => $fieldValue) {
            $setter = 'set' . $this->camelize($fieldName);
            if (!is_callable([$formField, $setter])) {
                continue;
            }
            $formField->$setter($fieldValue);
        }

        return $formField;
    }

    /**
     * {@inheritdoc}
     */
    public function formHasAvailableConfigurationFile(int $formId)
    {
        return file_exists($this->getConfigurationPathOfForm($formId));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationPathOfForm(int $formId)
    {
        return Configuration::STORE_PATH . '/main_' . $formId . '.yml';
    }

    /**
     * {@inheritdoc}
     */
    public function storeFormData(FormInterface $form)
    {
        $data = [
            'config'            => $form->getConfig(),
            'conditional_logic' => $form->getConditionalLogic(),
            'fields'            => $this->getFormFieldData($form)
        ];

        $this->storeYmlData($form, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFormData(FormInterface $form)
    {
        if (file_exists(Configuration::STORE_PATH . '/main_' . $form->getId() . '.yml')) {
            unlink(Configuration::STORE_PATH . '/main_' . $form->getId() . '.yml');
        }
    }

    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    protected function camelize($input, $separator = '_')
    {
        return ucfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getFormFieldData(FormInterface $form)
    {
        $formFields = [];

        /** @var FormFieldInterface $field */
        foreach ($form->getFields() as $field) {
            $formFields[] = $field->toArray();
        }

        return $formFields;
    }

    /**
     * @param FormInterface $form
     * @param mixed         $data
     */
    protected function storeYmlData(FormInterface $form, $data)
    {
        if (file_exists(Configuration::STORE_PATH . '/main_' . $form->getId() . '.yml')) {
            unlink(Configuration::STORE_PATH . '/main_' . $form->getId() . '.yml');
        }

        $yml = Yaml::dump($data);
        file_put_contents(Configuration::STORE_PATH . '/main_' . $form->getId() . '.yml', $yml);
    }
}
